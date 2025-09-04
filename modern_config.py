"""
Modern configuration for Request-based Mackolik Bot
"""
import os
from pathlib import Path
from typing import Optional, List, Dict, Any
from pydantic import BaseSettings, Field
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

class ModernSettings(BaseSettings):
    """Modern application settings"""
    
    # Application info
    app_name: str = Field(default="Mackolik Request Bot", env="APP_NAME")
    app_version: str = Field(default="3.0.0", env="APP_VERSION")
    app_author: str = Field(default="mebularts", env="APP_AUTHOR")
    
    # API endpoints
    mackolik_base_url: str = Field(default="https://www.mackolik.com", env="MACKOLIK_BASE_URL")
    mackolik_register_url: str = Field(default="https://www.mackolik.com/api/register", env="MACKOLIK_REGISTER_URL")
    mackolik_login_url: str = Field(default="https://www.mackolik.com/api/login", env="MACKOLIK_LOGIN_URL")
    
    # Request settings
    request_timeout: int = Field(default=30, env="REQUEST_TIMEOUT")
    max_retries: int = Field(default=3, env="MAX_RETRIES")
    retry_delay: float = Field(default=1.0, env="RETRY_DELAY")
    concurrent_requests: int = Field(default=5, env="CONCURRENT_REQUESTS")
    
    # User agent settings
    use_random_user_agent: bool = Field(default=True, env="USE_RANDOM_USER_AGENT")
    custom_user_agent: str = Field(default="", env="CUSTOM_USER_AGENT")
    
    # Proxy settings
    use_proxy: bool = Field(default=False, env="USE_PROXY")
    proxy_rotation: bool = Field(default=True, env="PROXY_ROTATION")
    proxy_timeout: int = Field(default=10, env="PROXY_TIMEOUT")
    
    # Account generation settings
    min_password_length: int = Field(default=8, env="MIN_PASSWORD_LENGTH")
    max_password_length: int = Field(default=16, env="MAX_PASSWORD_LENGTH")
    use_strong_passwords: bool = Field(default=True, env="USE_STRONG_PASSWORDS")
    
    # Turkish names data
    turkish_male_names: List[str] = Field(default_factory=lambda: [
        "Ahmet", "Mehmet", "Mustafa", "Ali", "Hüseyin", "İbrahim", "Osman", "Yusuf", "Murat", "İsmail",
        "Süleyman", "Fatih", "Orhan", "İsmet", "Soner", "Berkay", "Emir", "Yasin", "Can", "Mert",
        "Onur", "Ömer", "Eren", "Kaan", "Deniz", "Yunus", "Umut", "Serkan", "Ege", "Alper",
        "Hakan", "Barış", "Oğuz", "İlker", "Yavuz", "Volkan", "Gökhan", "Tolga", "Taha", "Furkan",
        "Berk", "Görkem", "Efe", "Kerem", "Tunç", "Emre", "Arda", "Özgür", "Uğur", "Tarık",
        "Fırat", "Cem", "Koray", "Bora", "Samet", "Doğukan", "Cihan", "Eray", "Enes", "Oğuzhan",
        "Burak", "Serhat", "Emirhan", "Berkcan", "Ufuk", "Caner", "Gökay", "Ertuğrul", "Şükrü", "Ozan",
        "Cemil", "Hasan", "İdris", "Kemal", "İlyas", "Adem", "Erkan", "Kadir", "Sercan", "Halil",
        "Okan", "İskender", "Metin", "Mahmut", "Tayfun", "Rıza", "Turgay", "Necati", "Kazım", "Doğan",
        "Ercan", "Bilal", "Gürkan", "Erhan", "Özkan", "Kutlu", "Şafak", "Emrah", "Bülent", "Salih",
        "Sami", "Ferhat", "Celal", "Adnan", "Burhan", "Zafer", "Abdullah", "Muammer", "İlhan", "Yiğit",
        "Özcan", "Arif"
    ])
    
    turkish_surnames: List[str] = Field(default_factory=lambda: [
        "Yılmaz", "Demir", "Öztürk", "Kaya", "Çelik", "Arslan", "Şahin", "Kılıç", "Koç", "Yıldız",
        "Doğan", "Güler", "Aktaş", "Ay", "Karadağ", "Aslan", "Güneş", "Kara", "Taş", "Sarı",
        "Oğuz", "Uzun", "Gündüz", "Gür", "Yalçın", "Erdoğan", "Demirci", "Kurt", "Kaplan", "Yavuz",
        "Aydın", "Özdemir", "Çetin", "Baran", "Toprak", "Şimşek", "Pala", "Çınar", "Turan", "Yıldırım",
        "Adıgüzel", "Akkuş", "Büyük", "Cengiz", "Eroğlu", "Gül", "Güzel", "Işık", "Keleş", "Kılınç",
        "Korkmaz", "Okur", "Sağlam", "Taşdemir", "Ünal", "Yaman", "Acar", "Ateş", "Bulut", "Çiçek",
        "Doğru", "Koçak", "Kurtuluş", "Küçük", "Orhan", "Eren", "Köse", "Polat", "Yüksel", "Özkan",
        "Türk", "Bozkurt", "Işık", "Özdemir", "Şahin"
    ])
    
    # Turkish months
    turkish_months: List[str] = Field(default_factory=lambda: [
        "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", 
        "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"
    ])
    
    # File paths
    output_dir: Path = Field(default=Path("output"), env="OUTPUT_DIR")
    logs_dir: Path = Field(default=Path("logs"), env="LOGS_DIR")
    config_dir: Path = Field(default=Path("config"), env="CONFIG_DIR")
    
    # UI settings
    window_width: int = Field(default=1000, env="WINDOW_WIDTH")
    window_height: int = Field(default=700, env="WINDOW_HEIGHT")
    theme: str = Field(default="dark", env="THEME")
    
    class Config:
        env_file = ".env"
        case_sensitive = False

# Global settings instance
settings = ModernSettings()