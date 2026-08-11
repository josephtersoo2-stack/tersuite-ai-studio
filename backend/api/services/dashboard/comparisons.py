"""
Comparison Metric Calculation Helper for Dashboard Aggregation.
Calculates previous period comparisons, percentage changes, and trend indicators.
"""

def calculate_comparison(current_value, previous_value):
    """
    Computes change percentage and trend indicator between current and previous period values.

    Returns:
        dict: {
            "value": int|float,
            "previous_value": int|float,
            "change_percent": float|None,
            "trend": "up"|"down"|"neutral"
        }
    """
    current_val = current_value if current_value is not None else 0
    prev_val = previous_value if previous_value is not None else 0

    if prev_val > 0:
        change_pct = round(((float(current_val) - float(prev_val)) / float(prev_val)) * 100, 2)
    else:
        change_pct = None  # Undefined when previous value is 0

    if current_val > prev_val:
        trend = "up"
    elif current_val < prev_val:
        trend = "down"
    else:
        trend = "neutral"

    return {
        "value": current_val,
        "previous_value": prev_val,
        "change_percent": change_pct,
        "trend": trend,
    }
