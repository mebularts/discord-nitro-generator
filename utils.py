"""
Utility functions for MackolApp
"""
import uuid
import json
import logging
from pathlib import Path
from typing import Optional, Dict, Any, List
from loguru import logger
from config import settings

def get_mac_address() -> str:
    """Get the MAC address of the current machine"""
    mac_address = ':'.join([
        '{:02x}'.format((uuid.getnode() >> elements) & 0xff) 
        for elements in range(0, 2 * 6, 2)
    ])
    return mac_address

def load_json_file(file_path: Path) -> Optional[Dict[str, Any]]:
    """Load JSON data from file"""
    try:
        if file_path.exists():
            with open(file_path, 'r', encoding='utf-8') as f:
                return json.load(f)
    except (FileNotFoundError, json.JSONDecodeError) as e:
        logger.warning(f"Could not load {file_path}: {e}")
    return None

def save_json_file(file_path: Path, data: Dict[str, Any]) -> bool:
    """Save data to JSON file"""
    try:
        with open(file_path, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=2, ensure_ascii=False)
        return True
    except Exception as e:
        logger.error(f"Could not save {file_path}: {e}")
        return False

def load_license() -> Optional[str]:
    """Load license key from file"""
    data = load_json_file(settings.license_file)
    return data.get("license_key") if data else None

def save_license(license_key: str) -> bool:
    """Save license key to file"""
    return save_json_file(settings.license_file, {"license_key": license_key})

def load_proxy() -> Optional[str]:
    """Load proxy from file"""
    data = load_json_file(settings.proxy_file)
    return data.get("proxy") if data else None

def save_proxy(proxy: str) -> bool:
    """Save proxy to file"""
    return save_json_file(settings.proxy_file, {"proxy": proxy})

def setup_logging() -> None:
    """Setup logging configuration"""
    logger.remove()  # Remove default handler
    logger.add(
        "logs/mackolapp.log",
        rotation="10 MB",
        retention="7 days",
        level="INFO",
        format="{time:YYYY-MM-DD HH:mm:ss} | {level} | {name}:{function}:{line} | {message}",
        encoding="utf-8"
    )
    logger.add(
        lambda msg: print(msg, end=""),
        level="INFO",
        format="<green>{time:HH:mm:ss}</green> | <level>{level}</level> | {message}"
    )

def validate_email_password_format(line: str) -> tuple[Optional[str], Optional[str]]:
    """Validate and parse email:password format"""
    try:
        if ':' not in line:
            return None, None
        email, password = line.strip().split(':', 1)
        if not email or not password:
            return None, None
        return email.strip(), password.strip()
    except Exception:
        return None, None

def generate_turkish_name() -> tuple[str, str]:
    """Generate random Turkish name and surname"""
    import random
    first_name = random.choice(settings.turkish_male_names)
    last_name = random.choice(settings.turkish_surnames)
    return first_name, last_name

def generate_username() -> str:
    """Generate random username"""
    from faker import Faker
    import random
    fake = Faker()
    return f"{fake.user_name()}{random.randint(10, 99)}"

def generate_birth_date() -> tuple[int, str, int]:
    """Generate random birth date"""
    import random
    day = random.randint(1, 28)
    month = random.choice(settings.turkish_months)
    year = random.randint(1975, 2003)
    return day, month, year

def create_output_filename(base_name: str, extension: str = "txt") -> str:
    """Create output filename with timestamp"""
    from datetime import datetime
    timestamp = datetime.now().strftime("%y%m%d_%H%M%S")
    return f"{base_name}_{timestamp}.{extension}"

def ensure_directory_exists(directory: Path) -> None:
    """Ensure directory exists, create if not"""
    directory.mkdir(parents=True, exist_ok=True)