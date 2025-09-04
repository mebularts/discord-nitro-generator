"""
Modern utility functions for Request-based Mackolik Bot
"""
import asyncio
import json
import random
import string
import time
from datetime import datetime
from pathlib import Path
from typing import Optional, Dict, Any, List, Tuple
from urllib.parse import urljoin, urlparse
import uuid

import httpx
from fake_useragent import UserAgent
from loguru import logger
from tenacity import retry, stop_after_attempt, wait_exponential, retry_if_exception_type

from modern_config import settings

class ModernUtils:
    """Modern utility class with advanced features"""
    
    def __init__(self):
        self.ua = UserAgent() if settings.use_random_user_agent else None
        self.session_cookies: Dict[str, Any] = {}
        
    def get_random_user_agent(self) -> str:
        """Get random user agent"""
        if self.ua:
            try:
                return self.ua.random
            except:
                pass
        
        # Fallback user agents
        user_agents = [
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15"
        ]
        return random.choice(user_agents)
    
    def generate_strong_password(self, length: int = None) -> str:
        """Generate strong password"""
        if length is None:
            length = random.randint(settings.min_password_length, settings.max_password_length)
        
        if settings.use_strong_passwords:
            # Strong password with mixed characters
            lowercase = string.ascii_lowercase
            uppercase = string.ascii_uppercase
            digits = string.digits
            symbols = "!@#$%^&*"
            
            # Ensure at least one character from each category
            password = [
                random.choice(lowercase),
                random.choice(uppercase),
                random.choice(digits),
                random.choice(symbols)
            ]
            
            # Fill the rest randomly
            all_chars = lowercase + uppercase + digits + symbols
            for _ in range(length - 4):
                password.append(random.choice(all_chars))
            
            random.shuffle(password)
            return ''.join(password)
        else:
            # Simple password
            chars = string.ascii_letters + string.digits
            return ''.join(random.choice(chars) for _ in range(length))
    
    def generate_turkish_name(self) -> Tuple[str, str]:
        """Generate random Turkish name and surname"""
        first_name = random.choice(settings.turkish_male_names)
        last_name = random.choice(settings.turkish_surnames)
        return first_name, last_name
    
    def generate_username(self, first_name: str = None, last_name: str = None) -> str:
        """Generate username"""
        if first_name and last_name:
            # Use name-based username
            base = f"{first_name.lower()}{last_name.lower()}"
            numbers = random.randint(10, 9999)
            return f"{base}{numbers}"
        else:
            # Random username
            adjectives = ["cool", "fast", "smart", "quick", "bold", "brave", "calm", "wise"]
            nouns = ["tiger", "eagle", "wolf", "lion", "bear", "fox", "hawk", "shark"]
            adjective = random.choice(adjectives)
            noun = random.choice(nouns)
            numbers = random.randint(10, 9999)
            return f"{adjective}{noun}{numbers}"
    
    def generate_birth_date(self) -> Tuple[int, str, int]:
        """Generate random birth date"""
        day = random.randint(1, 28)
        month = random.choice(settings.turkish_months)
        year = random.randint(1975, 2003)
        return day, month, year
    
    def generate_email_variants(self, base_email: str) -> List[str]:
        """Generate email variants for testing"""
        variants = []
        name, domain = base_email.split('@')
        
        # Add numbers
        for i in range(1, 6):
            variants.append(f"{name}{i}@{domain}")
        
        # Add dots
        if len(name) > 3:
            variants.append(f"{name[:2]}.{name[2:]}@{domain}")
        
        return variants
    
    def validate_email(self, email: str) -> bool:
        """Validate email format"""
        import re
        pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        return re.match(pattern, email) is not None
    
    def validate_proxy(self, proxy: str) -> bool:
        """Validate proxy format"""
        try:
            if '://' in proxy:
                parsed = urlparse(proxy)
                return parsed.hostname is not None and parsed.port is not None
            else:
                # Format: ip:port
                parts = proxy.split(':')
                if len(parts) == 2:
                    ip, port = parts
                    return all(c.isdigit() or c == '.' for c in ip) and port.isdigit()
        except:
            pass
        return False
    
    def parse_proxy_list(self, proxy_text: str) -> List[str]:
        """Parse proxy list from text"""
        proxies = []
        for line in proxy_text.split('\n'):
            line = line.strip()
            if line and self.validate_proxy(line):
                proxies.append(line)
        return proxies
    
    def get_proxy_dict(self, proxy: str) -> Dict[str, str]:
        """Convert proxy string to requests format"""
        if '://' in proxy:
            return {"http": proxy, "https": proxy}
        else:
            return {"http": f"http://{proxy}", "https": f"http://{proxy}"}
    
    def create_output_filename(self, base_name: str, extension: str = "txt") -> str:
        """Create output filename with timestamp"""
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        return f"{base_name}_{timestamp}.{extension}"
    
    def ensure_directory_exists(self, directory: Path) -> None:
        """Ensure directory exists"""
        directory.mkdir(parents=True, exist_ok=True)
    
    def save_json(self, data: Dict[str, Any], file_path: Path) -> bool:
        """Save data to JSON file"""
        try:
            self.ensure_directory_exists(file_path.parent)
            with open(file_path, 'w', encoding='utf-8') as f:
                json.dump(data, f, indent=2, ensure_ascii=False)
            return True
        except Exception as e:
            logger.error(f"Failed to save JSON: {e}")
            return False
    
    def load_json(self, file_path: Path) -> Optional[Dict[str, Any]]:
        """Load data from JSON file"""
        try:
            if file_path.exists():
                with open(file_path, 'r', encoding='utf-8') as f:
                    return json.load(f)
        except Exception as e:
            logger.error(f"Failed to load JSON: {e}")
        return None
    
    def save_accounts(self, accounts: List[Dict[str, str]], filename: str) -> bool:
        """Save accounts to file"""
        try:
            output_path = settings.output_dir / filename
            self.ensure_directory_exists(output_path.parent)
            
            with open(output_path, 'a', encoding='utf-8') as f:
                for account in accounts:
                    line = f"{account['email']}:{account['password']}"
                    if 'username' in account:
                        line += f":{account['username']}"
                    f.write(line + '\n')
            
            logger.info(f"Saved {len(accounts)} accounts to {output_path}")
            return True
        except Exception as e:
            logger.error(f"Failed to save accounts: {e}")
            return False
    
    def load_accounts_from_file(self, file_path: Path) -> List[Tuple[str, str]]:
        """Load accounts from file"""
        accounts = []
        try:
            if file_path.exists():
                with open(file_path, 'r', encoding='utf-8') as f:
                    for line in f:
                        line = line.strip()
                        if ':' in line:
                            parts = line.split(':', 1)
                            if len(parts) == 2:
                                accounts.append((parts[0].strip(), parts[1].strip()))
        except Exception as e:
            logger.error(f"Failed to load accounts: {e}")
        return accounts
    
    def get_csrf_token(self, response_text: str) -> Optional[str]:
        """Extract CSRF token from response"""
        import re
        patterns = [
            r'name="csrf_token"\s+value="([^"]+)"',
            r'name="_token"\s+value="([^"]+)"',
            r'"csrf_token":\s*"([^"]+)"',
            r'"_token":\s*"([^"]+)"',
            r'<meta name="csrf-token" content="([^"]+)"',
        ]
        
        for pattern in patterns:
            match = re.search(pattern, response_text)
            if match:
                return match.group(1)
        return None
    
    def extract_form_data(self, response_text: str, form_id: str = None) -> Dict[str, str]:
        """Extract form data from HTML"""
        import re
        form_data = {}
        
        # Extract all input fields
        input_pattern = r'<input[^>]*name="([^"]+)"[^>]*value="([^"]*)"[^>]*>'
        matches = re.findall(input_pattern, response_text)
        
        for name, value in matches:
            form_data[name] = value
        
        # Extract select fields
        select_pattern = r'<select[^>]*name="([^"]+)"[^>]*>.*?<option[^>]*selected[^>]*value="([^"]*)"'
        matches = re.findall(select_pattern, response_text, re.DOTALL)
        
        for name, value in matches:
            form_data[name] = value
        
        return form_data
    
    def random_delay(self, min_seconds: float = 0.5, max_seconds: float = 2.0):
        """Add random delay"""
        delay = random.uniform(min_seconds, max_seconds)
        time.sleep(delay)
    
    def generate_session_id(self) -> str:
        """Generate session ID"""
        return str(uuid.uuid4())
    
    def clean_text(self, text: str) -> str:
        """Clean text from HTML tags and extra whitespace"""
        import re
        # Remove HTML tags
        text = re.sub(r'<[^>]+>', '', text)
        # Clean whitespace
        text = ' '.join(text.split())
        return text.strip()

# Global utils instance
utils = ModernUtils()