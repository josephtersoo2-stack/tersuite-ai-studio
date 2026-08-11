"""
Multi-agent framework using CrewAI (open-source, MIT license).
Integrates with Django LLM registry for multi-provider support.
"""
import os
import logging
from typing import Optional, Dict, Any, List

# CrewAI imports
from crewai import Agent, Task, Crew, Process
from crewai.tools import tool as crew_tool

logger = logging.getLogger(__name__)


class AgentException(Exception):
    pass


def get_llm_config(provider_name: str, model_name: Optional[str] = None) -> Dict[str, Any]:
    """Load LLM config from Django registry and build CrewAI-compatible LLM config."""
    from llm_registry.models import LLMProvider, LLMModel
    try:
        provider = LLMProvider.objects.get(name=provider_name, enabled=True)
        if model_name:
            model = LLMModel.objects.get(provider=provider, model_id=model_name, enabled=True)
        else:
            model = LLMModel.objects.get(provider=provider, enabled=True, is_default=True)
        return {
            "provider": provider,
            "model": model,
            "model_name": model.model_id,
            "api_key_env": provider.api_key_env_var,
            "base_url": provider.api_base_url,
        }
    except Exception as e:
        logger.warning(f"LLM registry load failed: {e}")
        return {}


def build_llm_for_crew(config: Dict[str, Any]):
    """Build a LiteLLM-style LLM config string or object for CrewAI."""
    provider_name = config.get("provider")
    model_id = config.get("model_name", "")
    api_key_env = config.get("api_key_env", "")
    api_key = os.getenv(api_key_env)
    base_url = config.get("base_url", "")

    # For CrewAI, we construct the model identifier based on provider
    # CrewAI supports openai/, gemini/, anthropic/ prefixes via LiteLLM-style
    provider_map = {
        "openai": "openai",
        "gemini": "gemini",
        "google": "gemini",
        "anthropic": "anthropic",
        "glm": "openai/",  # GLM can be called via OpenAI-compatible endpoints
        "kimi": "openai/",
        "deepseek": "openai/",
    }
    prefix = provider_map.get(provider_name.name if hasattr(provider_name, 'name') else provider_name, "openai")

    # Return a model name that CrewAI can resolve (using LiteLLM convention)
    # If using direct SDK integration, this is handled differently
    return f"{prefix}{model_id}" if not model_id.startswith(prefix) else model_id


class TersuiteAIStudioCoordinator:
    """CrewAI-based coordinator for plugin generation pipeline."""

    def __init__(self, provider_name: str = "gemini", model_name: Optional[str] = None, workspace_path: Optional[str] = None):
        config = get_llm_config(provider_name, model_name)
        self.workspace_path = workspace_path or "/tmp/tersuite"
        os.makedirs(self.workspace_path, exist_ok=True)
        self.model_config = config
        self.llm_string = build_llm_for_crew(config) if config else None
        # In production: set LLM via environment or CrewAI config
        if self.llm_string and config:
            api_key_env = config.get("api_key_env")
            api_key = os.getenv(api_key_env) if api_key_env else None
            if api_key:
                os.environ[api_key_env] = api_key  # Ensure available

    def _safe_path(self, relative_path: str) -> str:
        relative_path = str(relative_path).replace("\\", "/").lstrip("/")
        if not relative_path or relative_path.startswith("../") or "/../" in relative_path or relative_path == "..":
            raise ValueError("Unsafe generated file path")
        target = os.path.abspath(os.path.join(self.workspace_path, relative_path))
        root = os.path.abspath(self.workspace_path)
        if target != root and not target.startswith(root + os.sep):
            raise ValueError("Generated file escapes workspace")
        return target

    def write_generated_file(self, relative_path: str, content: str) -> str:
        target = self._safe_path(relative_path)
        os.makedirs(os.path.dirname(target), exist_ok=True)
        with open(target, "w", encoding="utf-8") as handle:
            handle.write(content)
        return f"Wrote {relative_path}"

    def read_generated_file(self, relative_path: str) -> str:
        target = self._safe_path(relative_path)
        with open(target, "r", encoding="utf-8") as handle:
            return handle.read()

    def list_generated_files(self) -> List[str]:
        result = []
        for root, _, files in os.walk(self.workspace_path):
            for name in files:
                full = os.path.join(root, name)
                result.append(os.path.relpath(full, self.workspace_path).replace(os.sep, "/"))
        return sorted(result)

    def create_crew(self, project_context: Dict[str, Any], task_description: str) -> Crew:
        """Create a CrewAI crew with role-based agents for plugin generation."""

        # Coordinator / Planner
        planner = Agent(
            role="Plugin Planning Coordinator",
            goal="Plan WordPress plugin architecture with user satisfaction before coding",
            backstory="Expert WordPress architect who ensures plugins follow modern PHP 8.1+ standards, Zero-Trust security, and industry coding practices.",
            verbose=True,
            allow_delegation=True,
            llm=self.llm_string if self.llm_string else None,
        )

        # Sub-agents (specialists)
        ui_ux_agent = Agent(
            role="UI/UX Designer",
            goal="Design admin interfaces, settings pages, and user-facing elements for WordPress plugins",
            backstory="Specializes in clean, accessible WordPress admin UI with proper capability checks and nonces.",
            verbose=True,
            allow_delegation=False,
            llm=self.llm_string if self.llm_string else None,
        )

        backend_agent = Agent(
            role="PHP Backend Engineer",
            goal="Implement plugin logic using modern PHP namespaces, Composer autoload, prepared SQL statements, and secure hooks",
            backstory="Security-focused PHP developer using $wpdb->prepare(), sanitize_text_field, current_user_can(), and ABSPATH guards.",
            verbose=True,
            allow_delegation=False,
            llm=self.llm_string if self.llm_string else None,
        )

        frontend_agent = Agent(
            role="Frontend Developer",
            goal="Create CSS/JS assets optimized for WordPress admin and frontend use",
            backstory="Builds lightweight, secure assets that load only when needed via wp_enqueue_script/style.",
            verbose=True,
            allow_delegation=False,
            llm=self.llm_string if self.llm_string else None,
        )

        security_agent = Agent(
            role="WordPress Security Auditor",
            goal="Apply Zero-Trust security: nonces, capabilities, sanitization, escaping, and direct access prevention",
            backstory="Enforces 2026 WordPress security best practices: ABSPATH checks, wp_nonce verification, wp_kses, sanitize/escape functions.",
            verbose=True,
            allow_delegation=False,
            llm=self.llm_string if self.llm_string else None,
            tools=[read_tool, write_tool, list_tool],
        )

        write_tool = crew_tool(self.write_generated_file)
        read_tool = crew_tool(self.read_generated_file)
        list_tool = crew_tool(lambda: self.list_generated_files())

        coder_agent = Agent(
            role="Plugin Code Generator",
            goal="Generate complete plugin files following WordPress plugin repository standards",
            backstory="Uses pre-built boilerplate templates with modern PHP practices rather than rewriting from scratch.",
            verbose=True,
            allow_delegation=False,
            llm=self.llm_string if self.llm_string else None,
            tools=[write_tool, read_tool, list_tool],
        )

        review_agent = Agent(
            role="Code Reviewer",
            goal="Review all plugin code for errors, WP coding standards, security gaps, and missing capabilities",
            backstory="Audits plugins against WPScan vulnerability patterns and ensures PHPCS + WordPress ruleset compliance.",
            verbose=True,
            allow_delegation=False,
            llm=self.llm_string if self.llm_string else None,
            tools=[read_tool, write_tool, list_tool],
        )

        sandbox_agent = Agent(
            role="Sandbox Tester",
            goal="Deploy plugin to isolated microVM and run functional/security tests before delivery",
            backstory="Operates in isolated Firecracker/Kata containers, never on the host kernel. Tests plugin activation, settings, and database interactions.",
            verbose=True,
            allow_delegation=False,
            llm=self.llm_string if self.llm_string else None,
        )

        # Planning task (coordinator handles user interaction)
        planning_task = Task(
            description=f"Plan plugin architecture based on user request: {task_description}. Identify which sub-agents need to run.",
            agent=planner,
            expected_output="A structured plan with file list, security requirements, and sub-agent assignments.",
        )

        # Sub-tasks distributed to specialists
        tasks = [
            Task(
                description=f"Generate plugin structure and settings for: {task_description}",
                agent=ui_ux_agent,
                expected_output="Admin menu structure, settings page layout, and user interaction points.",
            ),
            Task(
                description=f"Build PHP backend logic, hooks, database interactions for: {task_description}",
                agent=backend_agent,
                expected_output="PHP classes, hooks, and database queries using $wpdb->prepare().",
            ),
            Task(
                description=f"Create CSS/JS assets for plugin admin and frontend",
                agent=frontend_agent,
                expected_output="Enqueued stylesheets and scripts following WP asset loading best practices.",
            ),
            Task(
                description=f"Audit all generated code for security vulnerabilities and apply fixes",
                agent=security_agent,
                expected_output="Security checklist: nonces verified, capabilities checked, inputs sanitized, outputs escaped.",
            ),
            Task(
                description=f"Generate the complete plugin for: {task_description}. You MUST use the write_generated_file tool to create every generated file in the workspace using exact relative paths. Do not only describe code. Preserve the final WordPress plugin directory structure and verify files with list_generated_files.",
                agent=coder_agent,
                expected_output="Complete plugin files ready for packaging.",
            ),
            Task(
                description=f"Review the actual files in the workspace against WP coding standards and security requirements. Use read_generated_file and write_generated_file to fix any issues you find.",
                agent=review_agent,
                expected_output="Review report with any errors or fixes needed.",
            ),
            Task(
                description=f"Deploy plugin to isolated microVM workspace and run tests",
                agent=sandbox_agent,
                expected_output="Test result: passed/failed, error list, workspace cleaned after.",
            ),
        ]

        # Crew with hierarchical process (coordinator manages sub-agents)
        # We use sequential process with coordinator oversight
        crew = Crew(
            agents=[planner, ui_ux_agent, backend_agent, frontend_agent,
                    security_agent, coder_agent, review_agent, sandbox_agent],
            tasks=[planning_task] + tasks,
            process=Process.sequential,
            verbose=2,
            # Memory settings (CrewAI built-in)
            memory=True,
            # Full output tracking
            full_output=True,
        )

        return crew

    def run_pipeline(self, project_context: Dict[str, Any], task_description: str) -> Dict[str, Any]:
        """Run the full CrewAI pipeline and return structured results."""
        logger.info(f"Starting CrewAI pipeline: {task_description}")
        self.workspace_path = project_context.get("workspace_path", self.workspace_path)
        os.makedirs(self.workspace_path, exist_ok=True)
        crew = self.create_crew(project_context, task_description)
        result = crew.kickoff(inputs={
            "project_context": project_context,
            "task_description": task_description,
            "workspace": project_context.get("workspace_path", "/tmp/test"),
        })
        logger.info(f"CrewAI pipeline completed for project: {project_context.get('project_id')}")
        return {
            "status": "completed",
            "raw_result": str(result),
            "project_context": project_context,
            "pipeline_type": "crewai_multi_agent",
        }
