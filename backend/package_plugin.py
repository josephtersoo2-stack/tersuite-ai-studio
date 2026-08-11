import os
import zipfile
from pathlib import Path

def create_plugin_zip():
    base_dir = Path(__file__).resolve().parent.parent
    plugin_dir = base_dir / "wp-plugin" / "tersostudio"
    zip_path = base_dir / "wp-plugin" / "tersostudio.zip"

    if zip_path.exists():
        zip_path.unlink()

    print(f"Zipping {plugin_dir} -> {zip_path}")
    with zipfile.ZipFile(zip_path, "w", zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(plugin_dir):
            for file in files:
                full_path = Path(root) / file
                # Compute relative path inside the zip using forward slashes
                rel_path = full_path.relative_to(plugin_dir.parent)
                archive_name = rel_path.as_posix() # Ensures forward slashes /
                zipf.write(full_path, archive_name)
                print(f"  Added: {archive_name}")

    print(f"SUCCESS: Created {zip_path}")

if __name__ == "__main__":
    create_plugin_zip()
