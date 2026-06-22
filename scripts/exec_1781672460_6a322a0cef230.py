
from scapy.all import IP, UDP, DNS, DNSQR, send
import time

def stress_dns_test(target_ip):
    print(f"[*] Initiating DNS stress probe on {target_ip}...")
    # حزمة استعلام DNS من نوع ANY لطلب كامل سجلات النطاق
    packet = IP(dst=target_ip) / UDP(dport=53) / DNS(rd=1, qd=DNSQR(qname="y.net", qtype="ANY"))
    
    # إرسال 100 حزمة بسرعة للتأكد من قدرة الخادم على المعالجة
    for i in range(100):
        send(packet, verbose=0)
    print("[*] Probes sent. Check server load or responsiveness.")

# stress_dns_test("172.18.0.1")
