"""
Advanced Request-based Mackolik Account Creation Bot
"""
import asyncio
import json
import random
import time
from datetime import datetime
from pathlib import Path
from typing import Optional, Dict, Any, List, Tuple
from urllib.parse import urljoin

import httpx
from loguru import logger
from tenacity import retry, stop_after_attempt, wait_exponential, retry_if_exception_type

from modern_config import settings
from modern_utils import utils

class MackolikRequestBot:
    """Advanced request-based Mackolik account creation bot"""
    
    def __init__(self, proxy: Optional[str] = None):
        self.proxy = proxy
        self.session: Optional[httpx.AsyncClient] = None
        self.csrf_token: Optional[str] = None
        self.session_cookies: Dict[str, str] = {}
        
    async def __aenter__(self):
        """Async context manager entry"""
        await self.create_session()
        return self
    
    async def __aexit__(self, exc_type, exc_val, exc_tb):
        """Async context manager exit"""
        await self.close_session()
    
    async def create_session(self):
        """Create HTTP session with proper headers"""
        headers = {
            'User-Agent': utils.get_random_user_agent(),
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'tr-TR,tr;q=0.9,en;q=0.8',
            'Accept-Encoding': 'gzip, deflate, br',
            'DNT': '1',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
            'Sec-Fetch-Dest': 'document',
            'Sec-Fetch-Mode': 'navigate',
            'Sec-Fetch-Site': 'none',
            'Cache-Control': 'max-age=0'
        }
        
        # Configure proxy
        proxies = None
        if self.proxy and utils.validate_proxy(self.proxy):
            proxies = utils.get_proxy_dict(self.proxy)
        
        # Create session
        self.session = httpx.AsyncClient(
            headers=headers,
            proxies=proxies,
            timeout=settings.request_timeout,
            follow_redirects=True,
            verify=False  # For development, use True in production
        )
        
        logger.info("HTTP session created successfully")
    
    async def close_session(self):
        """Close HTTP session"""
        if self.session:
            await self.session.aclose()
            logger.info("HTTP session closed")
    
    @retry(
        stop=stop_after_attempt(settings.max_retries),
        wait=wait_exponential(multiplier=1, min=4, max=10),
        retry=retry_if_exception_type((httpx.RequestError, httpx.TimeoutException))
    )
    async def make_request(
        self, 
        method: str, 
        url: str, 
        **kwargs
    ) -> httpx.Response:
        """Make HTTP request with retry logic"""
        try:
            if not self.session:
                await self.create_session()
            
            response = await self.session.request(method, url, **kwargs)
            response.raise_for_status()
            return response
            
        except httpx.HTTPStatusError as e:
            logger.error(f"HTTP error {e.response.status_code}: {e.response.text}")
            raise
        except httpx.RequestError as e:
            logger.error(f"Request error: {e}")
            raise
        except Exception as e:
            logger.error(f"Unexpected error: {e}")
            raise
    
    async def get_csrf_token(self) -> Optional[str]:
        """Get CSRF token from registration page"""
        try:
            # Try to get registration page
            response = await self.make_request(
                'GET', 
                f"{settings.mackolik_base_url}/canli-sonuclar"
            )
            
            # Extract CSRF token
            self.csrf_token = utils.get_csrf_token(response.text)
            
            if self.csrf_token:
                logger.info("CSRF token obtained successfully")
                return self.csrf_token
            else:
                logger.warning("CSRF token not found, trying alternative method")
                return await self.get_csrf_token_alternative()
                
        except Exception as e:
            logger.error(f"Failed to get CSRF token: {e}")
            return None
    
    async def get_csrf_token_alternative(self) -> Optional[str]:
        """Alternative method to get CSRF token"""
        try:
            # Try API endpoint
            response = await self.make_request(
                'GET',
                f"{settings.mackolik_base_url}/api/csrf-token"
            )
            
            data = response.json()
            self.csrf_token = data.get('csrf_token') or data.get('token')
            
            if self.csrf_token:
                logger.info("CSRF token obtained via API")
                return self.csrf_token
            
        except Exception as e:
            logger.warning(f"Alternative CSRF token method failed: {e}")
        
        return None
    
    async def check_email_availability(self, email: str) -> bool:
        """Check if email is available"""
        try:
            response = await self.make_request(
                'POST',
                f"{settings.mackolik_base_url}/api/check-email",
                json={'email': email}
            )
            
            data = response.json()
            return data.get('available', True)
            
        except Exception as e:
            logger.warning(f"Email availability check failed: {e}")
            return True  # Assume available if check fails
    
    async def check_username_availability(self, username: str) -> bool:
        """Check if username is available"""
        try:
            response = await self.make_request(
                'POST',
                f"{settings.mackolik_base_url}/api/check-username",
                json={'username': username}
            )
            
            data = response.json()
            return data.get('available', True)
            
        except Exception as e:
            logger.warning(f"Username availability check failed: {e}")
            return True  # Assume available if check fails
    
    async def create_account(
        self, 
        email: str, 
        password: str,
        first_name: str = None,
        last_name: str = None,
        username: str = None
    ) -> Tuple[bool, str, Dict[str, Any]]:
        """
        Create Mackolik account using requests
        
        Args:
            email: Email address
            password: Password
            first_name: First name (optional)
            last_name: Last name (optional)
            username: Username (optional)
            
        Returns:
            Tuple[bool, str, Dict]: (success, message, account_data)
        """
        try:
            logger.info(f"Creating account for: {email}")
            
            # Generate account data if not provided
            if not first_name or not last_name:
                first_name, last_name = utils.generate_turkish_name()
            
            if not username:
                username = utils.generate_username(first_name, last_name)
            
            # Check availability
            if not await self.check_email_availability(email):
                return False, "Email already exists", {}
            
            if not await self.check_username_availability(username):
                # Generate new username
                username = utils.generate_username(first_name, last_name)
                if not await self.check_username_availability(username):
                    return False, "Username not available", {}
            
            # Get CSRF token
            if not self.csrf_token:
                await self.get_csrf_token()
            
            # Prepare registration data
            day, month, year = utils.generate_birth_date()
            
            registration_data = {
                'email': email,
                'password': password,
                'password_confirmation': password,
                'first_name': first_name,
                'last_name': last_name,
                'username': username,
                'gender': 'male',
                'birth_day': day,
                'birth_month': month,
                'birth_year': year,
                'terms_accepted': '1',
                'privacy_accepted': '1',
                'marketing_accepted': '0'
            }
            
            # Add CSRF token if available
            if self.csrf_token:
                registration_data['_token'] = self.csrf_token
                registration_data['csrf_token'] = self.csrf_token
            
            # Make registration request
            response = await self.make_request(
                'POST',
                f"{settings.mackolik_base_url}/api/register",
                json=registration_data,
                headers={
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': self.csrf_token or ''
                }
            )
            
            # Parse response
            try:
                response_data = response.json()
            except:
                response_data = {'message': response.text}
            
            # Check if registration was successful
            if response.status_code == 201 or response.status_code == 200:
                if response_data.get('success') or 'success' in response_data.get('message', '').lower():
                    account_data = {
                        'email': email,
                        'password': password,
                        'username': username,
                        'first_name': first_name,
                        'last_name': last_name,
                        'created_at': datetime.now().isoformat(),
                        'status': 'created'
                    }
                    
                    logger.info(f"Account created successfully: {email}")
                    return True, "Account created successfully", account_data
                else:
                    error_msg = response_data.get('message', 'Unknown error')
                    logger.error(f"Registration failed: {error_msg}")
                    return False, error_msg, {}
            else:
                error_msg = response_data.get('message', f'HTTP {response.status_code}')
                logger.error(f"Registration failed with status {response.status_code}: {error_msg}")
                return False, error_msg, {}
                
        except Exception as e:
            logger.error(f"Error creating account for {email}: {e}")
            return False, f"Error: {str(e)}", {}
    
    async def verify_account(self, email: str, password: str) -> Tuple[bool, str]:
        """Verify account by attempting login"""
        try:
            # Prepare login data
            login_data = {
                'email': email,
                'password': password
            }
            
            if self.csrf_token:
                login_data['_token'] = self.csrf_token
            
            # Attempt login
            response = await self.make_request(
                'POST',
                f"{settings.mackolik_base_url}/api/login",
                json=login_data,
                headers={
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': self.csrf_token or ''
                }
            )
            
            if response.status_code == 200:
                try:
                    response_data = response.json()
                    if response_data.get('success') or 'token' in response_data:
                        logger.info(f"Account verified successfully: {email}")
                        return True, "Account verified successfully"
                except:
                    pass
            
            logger.warning(f"Account verification failed: {email}")
            return False, "Account verification failed"
            
        except Exception as e:
            logger.error(f"Error verifying account {email}: {e}")
            return False, f"Error: {str(e)}"
    
    async def create_multiple_accounts(
        self,
        email_password_list: List[Tuple[str, str]],
        progress_callback: Optional[callable] = None
    ) -> Tuple[int, int, List[Dict[str, Any]]]:
        """
        Create multiple accounts concurrently
        
        Args:
            email_password_list: List of (email, password) tuples
            progress_callback: Callback function for progress updates
            
        Returns:
            Tuple[int, int, List]: (successful_count, failed_count, created_accounts)
        """
        successful = 0
        failed = 0
        created_accounts = []
        
        # Create semaphore to limit concurrent requests
        semaphore = asyncio.Semaphore(settings.concurrent_requests)
        
        async def create_single_account(email: str, password: str, index: int):
            nonlocal successful, failed
            
            async with semaphore:
                try:
                    success, message, account_data = await self.create_account(email, password)
                    
                    if success:
                        successful += 1
                        created_accounts.append(account_data)
                        logger.info(f"✓ Account {index+1} created: {email}")
                    else:
                        failed += 1
                        logger.error(f"✗ Account {index+1} failed: {email} - {message}")
                    
                    # Progress callback
                    if progress_callback:
                        progress_callback(index + 1, len(email_password_list), email, success)
                    
                    # Random delay between requests
                    await asyncio.sleep(random.uniform(0.5, 2.0))
                    
                except Exception as e:
                    failed += 1
                    logger.error(f"Error processing account {index+1} ({email}): {e}")
                    
                    if progress_callback:
                        progress_callback(index + 1, len(email_password_list), email, False)
        
        # Create tasks for all accounts
        tasks = [
            create_single_account(email, password, i)
            for i, (email, password) in enumerate(email_password_list)
        ]
        
        # Execute all tasks
        await asyncio.gather(*tasks, return_exceptions=True)
        
        logger.info(f"Account creation completed: {successful} successful, {failed} failed")
        return successful, failed, created_accounts
    
    async def test_connection(self) -> bool:
        """Test connection to Mackolik"""
        try:
            response = await self.make_request('GET', settings.mackolik_base_url)
            return response.status_code == 200
        except Exception as e:
            logger.error(f"Connection test failed: {e}")
            return False