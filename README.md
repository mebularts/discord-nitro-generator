# MackolApp v2.0.0

Modern, advanced Mackolik account creation and management tool with improved UI, error handling, and performance.

## Features

### 🚀 Modern Architecture
- **Type Hints**: Full type annotation support for better code maintainability
- **Configuration Management**: Centralized settings with Pydantic validation
- **Logging**: Advanced logging with Loguru for better debugging
- **Error Handling**: Comprehensive error handling and recovery mechanisms

### 🎨 Modern UI/UX
- **Material Design**: Modern, responsive interface with dark theme
- **Progress Tracking**: Real-time progress bars and status updates
- **Tabbed Interface**: Organized workflow with separate tabs for different operations
- **File Management**: Drag-and-drop file selection with validation

### 🔧 Advanced Functionality
- **Multi-Provider Support**: Gmail and Outlook/Hotmail account creation
- **Proxy Support**: Built-in proxy rotation and validation
- **Account Verification**: Comprehensive account checking and validation
- **Background Processing**: Multi-threaded operations for better performance

### 🛡️ Security & Reliability
- **License Verification**: Secure license validation system
- **Input Validation**: Comprehensive input validation and sanitization
- **Error Recovery**: Automatic error recovery and retry mechanisms
- **Data Protection**: Secure handling of sensitive information

## Installation

### Prerequisites
- Python 3.8 or higher
- Chrome browser installed
- Valid license key

### Setup

1. **Clone or download the project**
```bash
git clone <repository-url>
cd mackolapp
```

2. **Install dependencies**
```bash
pip install -r requirements.txt
```

3. **Configure environment (optional)**
```bash
cp .env.example .env
# Edit .env file with your settings
```

4. **Run the application**
```bash
python main.py
```

## Usage

### 1. License Verification
- Enter your license key when prompted
- Contact via Telegram if you need a license

### 2. Mackolik Account Creation
- **Tab**: "Mackolik Account Creation"
- **Input**: Number of accounts, output filename, email:password file
- **Proxy**: Optional proxy configuration
- **Output**: Created accounts saved to timestamped file

### 3. Outlook Account Creation
- **Tab**: "Outlook Account Creation"
- **Input**: Same as Mackolik but uses Outlook/Hotmail for verification
- **Features**: Optimized for Outlook email handling

### 4. Account Checking
- **Tab**: "Account Checker"
- **Input**: File with email:password list or paste directly
- **Output**: Separate files for successful, failed, and verification-required accounts

## File Formats

### Email:Password File
```
email1@domain.com:password1
email2@domain.com:password2
email3@domain.com:password3
```

### Proxy Format
```
ip1:port1,ip2:port2,ip3:port3
```

## Output Files

### Account Creation
- `{filename}_{timestamp}.txt` - Successfully created accounts
- `failed_accounts.txt` - Failed account creation attempts
- `failed_accounts_outlook.txt` - Failed Outlook account creation attempts

### Account Checking
- `successful_accounts.txt` - Valid accounts
- `failed_accounts.txt` - Invalid accounts
- `email_verification_required.txt` - Accounts requiring email verification
- `error_accounts.txt` - Accounts with errors

## Configuration

### Environment Variables
- `LICENSE_VERIFICATION_URL` - License verification endpoint
- `TELEGRAM_CONTACT` - Telegram contact URL
- `SELENIUM_TIMEOUT` - Selenium operation timeout
- `CHROME_HEADLESS` - Run Chrome in headless mode

### Settings File
Configuration is managed through `config.py` with Pydantic validation.

## Advanced Features

### Proxy Management
- Automatic proxy rotation
- Proxy validation and testing
- Support for HTTP/HTTPS proxies

### Error Handling
- Comprehensive error logging
- Automatic retry mechanisms
- Graceful failure handling

### Performance Optimization
- Multi-threaded operations
- Efficient Selenium management
- Memory optimization

## Troubleshooting

### Common Issues

1. **Chrome Driver Issues**
   - Ensure Chrome browser is installed
   - Update Chrome to latest version
   - Check internet connection

2. **License Verification Failed**
   - Verify license key is correct
   - Check internet connection
   - Contact support if issues persist

3. **Account Creation Fails**
   - Check email:password file format
   - Verify proxy settings if using
   - Check internet connection

4. **Email Verification Issues**
   - Ensure email accounts are accessible
   - Check email provider settings
   - Verify email:password combinations

### Logs
Check `logs/mackolapp.log` for detailed error information.

## Support

- **Telegram**: [@mebularts](https://t.me/mebularts)
- **Issues**: Report bugs and feature requests
- **Documentation**: Check this README for usage instructions

## Changelog

### v2.0.0
- Complete rewrite with modern Python practices
- New UI with Material Design
- Improved error handling and logging
- Multi-threaded operations
- Enhanced proxy support
- Better configuration management

### v1.x.x
- Legacy version with basic functionality

## License

This software requires a valid license key. Contact [@mebularts](https://t.me/mebularts) for licensing information.

## Disclaimer

This tool is for educational purposes only. Users are responsible for complying with all applicable laws and terms of service.