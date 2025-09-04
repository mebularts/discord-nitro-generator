"""
Setup script for MackolApp
"""
from setuptools import setup, find_packages
from pathlib import Path

# Read README
readme_path = Path(__file__).parent / "README.md"
long_description = readme_path.read_text(encoding="utf-8") if readme_path.exists() else ""

# Read requirements
requirements_path = Path(__file__).parent / "requirements.txt"
requirements = []
if requirements_path.exists():
    requirements = requirements_path.read_text(encoding="utf-8").strip().split('\n')
    requirements = [req.strip() for req in requirements if req.strip() and not req.startswith('#')]

setup(
    name="mackolapp",
    version="2.0.0",
    author="mebularts",
    author_email="contact@mebularts.com",
    description="Modern Mackolik account creation and management tool",
    long_description=long_description,
    long_description_content_type="text/markdown",
    url="https://github.com/mebularts/mackolapp",
    packages=find_packages(),
    classifiers=[
        "Development Status :: 4 - Beta",
        "Intended Audience :: Developers",
        "License :: Other/Proprietary License",
        "Operating System :: OS Independent",
        "Programming Language :: Python :: 3",
        "Programming Language :: Python :: 3.8",
        "Programming Language :: Python :: 3.9",
        "Programming Language :: Python :: 3.10",
        "Programming Language :: Python :: 3.11",
        "Topic :: Software Development :: Libraries :: Python Modules",
        "Topic :: Internet :: WWW/HTTP :: Browsers",
    ],
    python_requires=">=3.8",
    install_requires=requirements,
    extras_require={
        "dev": [
            "pytest>=6.0",
            "pytest-cov>=2.0",
            "black>=21.0",
            "flake8>=3.8",
            "mypy>=0.800",
        ],
    },
    entry_points={
        "console_scripts": [
            "mackolapp=main:main",
        ],
    },
    include_package_data=True,
    package_data={
        "": ["*.ico", "*.png", "*.xml"],
    },
    keywords="mackolik, selenium, automation, account-creation, web-scraping",
    project_urls={
        "Bug Reports": "https://github.com/mebularts/mackolapp/issues",
        "Source": "https://github.com/mebularts/mackolapp",
        "Documentation": "https://github.com/mebularts/mackolapp#readme",
    },
)