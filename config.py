"""
Configuration management for MackolApp
"""
import os
from pathlib import Path
from typing import Optional
from pydantic import BaseSettings, Field
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

class Settings(BaseSettings):
    """Application settings"""
    
    # License verification
    license_verification_url: str = Field(
        default="https://adofon.linkol.in/maclicense.php",
        env="LICENSE_VERIFICATION_URL"
    )
    
    # Telegram contact
    telegram_contact: str = Field(
        default="https://t.me/mebularts",
        env="TELEGRAM_CONTACT"
    )
    
    # Application settings
    app_name: str = Field(default="MackolApp", env="APP_NAME")
    app_version: str = Field(default="2.0.0", env="APP_VERSION")
    
    # File paths
    license_file: Path = Field(default=Path("license.json"))
    proxy_file: Path = Field(default=Path("proxy.json"))
    icon_file: Path = Field(default=Path("icon.ico"))
    
    # Selenium settings
    selenium_timeout: int = Field(default=20, env="SELENIUM_TIMEOUT")
    selenium_implicit_wait: int = Field(default=10, env="SELENIUM_IMPLICIT_WAIT")
    
    # Chrome options
    chrome_headless: bool = Field(default=False, env="CHROME_HEADLESS")
    chrome_disable_gpu: bool = Field(default=True, env="CHROME_DISABLE_GPU")
    chrome_no_sandbox: bool = Field(default=True, env="CHROME_NO_SANDBOX")
    
    # Turkish names data
    turkish_male_names: list[str] = Field(default_factory=lambda: [
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
    
    turkish_surnames: list[str] = Field(default_factory=lambda: [
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
    turkish_months: list[str] = Field(default_factory=lambda: [
        "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", 
        "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"
    ])
    
    class Config:
        env_file = ".env"
        case_sensitive = False

# Global settings instance
settings = Settings()