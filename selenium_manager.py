"""
Modern Selenium WebDriver management
"""
import os
import time
import random
from typing import Optional, List, Dict, Any
from contextlib import contextmanager
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options as ChromeOptions
from selenium.webdriver.chrome.service import Service
from selenium.common.exceptions import (
    NoSuchElementException, 
    TimeoutException, 
    WebDriverException
)
from undetected_chromedriver import Chrome, ChromeOptions as UndetectedChromeOptions
from webdriver_manager.chrome import ChromeDriverManager
from loguru import logger
from config import settings

class SeleniumManager:
    """Modern Selenium WebDriver manager with error handling and optimization"""
    
    def __init__(self, proxy: Optional[str] = None, headless: bool = False):
        self.proxy = proxy
        self.headless = headless
        self.driver: Optional[webdriver.Chrome] = None
        
    def _get_chrome_options(self) -> ChromeOptions:
        """Get optimized Chrome options"""
        options = ChromeOptions()
        
        # Basic options
        if self.headless:
            options.add_argument("--headless")
        if settings.chrome_disable_gpu:
            options.add_argument("--disable-gpu")
        if settings.chrome_no_sandbox:
            options.add_argument("--no-sandbox")
            
        # Performance options
        options.add_argument("--disable-dev-shm-usage")
        options.add_argument("--disable-extensions")
        options.add_argument("--disable-plugins")
        options.add_argument("--disable-images")
        options.add_argument("--disable-javascript")
        options.add_argument("--disable-web-security")
        options.add_argument("--disable-features=VizDisplayCompositor")
        
        # User agent
        options.add_argument("--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36")
        
        # Window size
        options.add_argument("--window-size=1920,1080")
        options.add_argument("--start-maximized")
        
        # Proxy configuration
        if self.proxy:
            options.add_argument(f'--proxy-server={self.proxy}')
            
        # Additional preferences
        prefs = {
            "profile.default_content_setting_values": {
                "notifications": 2,
                "media_stream": 2,
            },
            "profile.managed_default_content_settings": {
                "images": 2
            }
        }
        options.add_experimental_option("prefs", prefs)
        
        return options
    
    def _get_undetected_chrome_options(self) -> UndetectedChromeOptions:
        """Get undetected Chrome options"""
        options = UndetectedChromeOptions()
        
        if self.headless:
            options.add_argument("--headless")
        if settings.chrome_disable_gpu:
            options.add_argument("--disable-gpu")
        if settings.chrome_no_sandbox:
            options.add_argument("--no-sandbox")
            
        options.add_argument("--disable-dev-shm-usage")
        options.add_argument("--disable-extensions")
        options.add_argument("--window-size=1920,1080")
        options.add_argument("--start-maximized")
        
        if self.proxy:
            options.add_argument(f'--proxy-server={self.proxy}')
            
        return options
    
    @contextmanager
    def get_driver(self, use_undetected: bool = True):
        """Context manager for WebDriver"""
        driver = None
        try:
            if use_undetected:
                options = self._get_undetected_chrome_options()
                driver = Chrome(options=options)
            else:
                options = self._get_chrome_options()
                service = Service(ChromeDriverManager().install())
                driver = webdriver.Chrome(service=service, options=options)
                
            driver.implicitly_wait(settings.selenium_implicit_wait)
            driver.set_page_load_timeout(settings.selenium_timeout)
            
            logger.info("WebDriver initialized successfully")
            yield driver
            
        except Exception as e:
            logger.error(f"Failed to initialize WebDriver: {e}")
            raise
        finally:
            if driver:
                try:
                    driver.quit()
                    logger.info("WebDriver closed successfully")
                except Exception as e:
                    logger.warning(f"Error closing WebDriver: {e}")
                finally:
                    # Force kill Chrome processes
                    self._cleanup_chrome_processes()
    
    def _cleanup_chrome_processes(self):
        """Clean up Chrome processes"""
        try:
            if os.name == 'nt':  # Windows
                os.system("taskkill /f /im chrome.exe 2>nul")
            else:  # Linux/Mac
                os.system("pkill -f chrome 2>/dev/null")
        except Exception as e:
            logger.warning(f"Error cleaning up Chrome processes: {e}")
    
    def wait_for_element(
        self, 
        driver: webdriver.Chrome, 
        by: By, 
        value: str, 
        timeout: int = None
    ) -> Any:
        """Wait for element to be present and return it"""
        timeout = timeout or settings.selenium_timeout
        try:
            element = WebDriverWait(driver, timeout).until(
                EC.presence_of_element_located((by, value))
            )
            return element
        except TimeoutException:
            logger.error(f"Element not found: {by}={value}")
            raise
    
    def wait_for_clickable(
        self, 
        driver: webdriver.Chrome, 
        by: By, 
        value: str, 
        timeout: int = None
    ) -> Any:
        """Wait for element to be clickable and return it"""
        timeout = timeout or settings.selenium_timeout
        try:
            element = WebDriverWait(driver, timeout).until(
                EC.element_to_be_clickable((by, value))
            )
            return element
        except TimeoutException:
            logger.error(f"Element not clickable: {by}={value}")
            raise
    
    def safe_click(self, driver: webdriver.Chrome, element: Any) -> bool:
        """Safely click an element"""
        try:
            driver.execute_script("arguments[0].click();", element)
            return True
        except Exception as e:
            logger.warning(f"Failed to click element: {e}")
            return False
    
    def safe_send_keys(self, element: Any, text: str, clear_first: bool = True) -> bool:
        """Safely send keys to an element"""
        try:
            if clear_first:
                element.clear()
            element.send_keys(text)
            return True
        except Exception as e:
            logger.warning(f"Failed to send keys: {e}")
            return False
    
    def handle_alert(self, driver: webdriver.Chrome) -> bool:
        """Handle browser alerts"""
        try:
            alert = driver.switch_to.alert
            alert.accept()
            logger.info("Alert accepted")
            return True
        except Exception:
            return False
    
    def random_delay(self, min_seconds: float = 1.0, max_seconds: float = 3.0):
        """Add random delay to mimic human behavior"""
        delay = random.uniform(min_seconds, max_seconds)
        time.sleep(delay)
    
    def scroll_to_element(self, driver: webdriver.Chrome, element: Any):
        """Scroll to element"""
        try:
            driver.execute_script("arguments[0].scrollIntoView(true);", element)
            self.random_delay(0.5, 1.0)
        except Exception as e:
            logger.warning(f"Failed to scroll to element: {e}")
    
    def take_screenshot(self, driver: webdriver.Chrome, filename: str = None) -> str:
        """Take screenshot for debugging"""
        if not filename:
            filename = f"screenshot_{int(time.time())}.png"
        
        try:
            screenshot_path = f"logs/{filename}"
            driver.save_screenshot(screenshot_path)
            logger.info(f"Screenshot saved: {screenshot_path}")
            return screenshot_path
        except Exception as e:
            logger.error(f"Failed to take screenshot: {e}")
            return ""