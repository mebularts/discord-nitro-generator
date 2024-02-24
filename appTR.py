import sys
import requests
import string
import random
from PyQt5.QtWidgets import QApplication, QMainWindow, QLabel, QPushButton, QVBoxLayout, QWidget, QSpinBox
from PyQt5.QtCore import QTimer, Qt, QUrl
from PyQt5.QtGui import QDesktopServices

DISCORD_WEBHOOK_URL = "YOUR_DISCORD_WEBHOOK_URL"
DEVELOPER_URL = "https://mebularts.com.tr"

class DiscordBot(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Discord Bot")

        self.uretilen_kod_sayisi = 0
        self.deneme_sayisi = 0
        self.onaylanan_kod_sayisi = 0

        self.central_widget = QWidget()
        self.setCentralWidget(self.central_widget)

        self.layout = QVBoxLayout()
        self.central_widget.setLayout(self.layout)

        self.uretilen_kod_label = QLabel(f"Üretilen Kod Sayısı: {self.uretilen_kod_sayisi}")
        self.layout.addWidget(self.uretilen_kod_label)

        self.deneme_label = QLabel(f"Deneme Yapılan Kod Sayısı: {self.deneme_sayisi}")
        self.layout.addWidget(self.deneme_label)

        self.onaylanan_kod_label = QLabel(f"Onaylanan Kod Sayısı: {self.onaylanan_kod_sayisi}")
        self.layout.addWidget(self.onaylanan_kod_label)
        
        self.bosluk_label = QLabel(f"--------------------------------------------------------")
        self.layout.addWidget(self.bosluk_label)
        
        self.kod_length_label = QLabel(f"https://discord.gift/(code_length) STD: 16/18")
        self.layout.addWidget(self.kod_length_label)
        
        self.kod_length_input = QSpinBox()
        self.kod_length_input.setMinimum(1)
        self.kod_length_input.setMaximum(100)
        self.kod_length_input.setValue(16)
        self.layout.addWidget(self.kod_length_input)
        
        self.bosluk_label = QLabel(f"--------------------------------------------------------")
        self.layout.addWidget(self.bosluk_label)

        self.baslat_button = QPushButton("Başlat")
        self.baslat_button.clicked.connect(self.baslat)
        self.layout.addWidget(self.baslat_button)

        self.durdur_button = QPushButton("Durdur")
        self.durdur_button.clicked.connect(self.durdur)
        self.layout.addWidget(self.durdur_button)

        self.developer_label = QLabel("<a href='{0}'>Developer: mebularts</a>".format(DEVELOPER_URL))
        self.developer_label.setTextFormat(Qt.RichText)
        self.developer_label.setTextInteractionFlags(Qt.TextBrowserInteraction)
        self.developer_label.setOpenExternalLinks(True)
        self.layout.addWidget(self.developer_label)

        self.timer = QTimer(self)
        self.timer.timeout.connect(self.kod_uret_ve_kontrol)

    def baslat(self):
        self.uretilen_kod_sayisi = 0
        self.deneme_sayisi = 0
        self.timer.start(1000)  # 1 saniyede bir çalışacak

    def durdur(self):
        self.timer.stop()

    def kod_uret_ve_kontrol(self):
        length = self.kod_length_input.value()
        uretilen_dize = self.generate_random_string(length)
        self.deneme_sayisi += 1
        self.uretilen_kod_sayisi += 1
        self.uretilen_kod_label.setText(f"Üretilen Kod Sayısı: {self.uretilen_kod_sayisi}")
        self.deneme_label.setText(f"Deneme Yapılan Kod Sayısı: {self.deneme_sayisi}")
        with open("uretilen.txt", "a") as f:
                f.write(uretilen_dize + "\n")

        url = f"https://discordapp.com/api/v9/entitlements/gift-codes/{uretilen_dize}?with_application=false&with_subscription_plan=true"
        response = requests.get(url)
        if response.status_code == 200:
            self.onaylanan_kod_sayisi += 1
            self.onaylanan_kod_label.setText(f"Onaylanan Kod Sayısı: {self.onaylanan_kod_sayisi}")

            with open("onaylanan.txt", "a") as f:
                f.write(uretilen_dize + "\n")

            data = {"content": uretilen_dize}
            requests.post(DISCORD_WEBHOOK_URL, json=data)

    def generate_random_string(self, length):
        characters = string.ascii_letters + string.digits
        return ''.join(random.choice(characters) for _ in range(length))

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = DiscordBot()
    window.show()
    sys.exit(app.exec_())
