
from scapy.all import IP, UDP, DNS, DNSQR, sr1

def test_dns_vulnerability(target_ip, query_domain):
    print(f"[*] Crafting malicious DNS query for {query_domain} targeting {target_ip}...")
    
    # بناء حزمة DNS مخصصة للاستعلام عن سجلات 'A'
    dns_packet = IP(dst=target_ip) / UDP(dport=53) / DNS(rd=1, qd=DNSQR(qname=query_domain))
    
    # إرسال الحزمة وانتظار الاستجابة
    response = sr1(dns_packet, timeout=2, verbose=0)
    
    if response and response.haslayer(DNS):
        print(f"[+] Response received from {target_ip}!")
        print(f"[*] Transaction ID: {response[DNS].id}")
        print(f"[*] Flags: {hex(response[DNS].flags)}")
        
        # التحقق من وجود إجابات قابلة للتعديل
        if response[DNS].ancount > 0:
            for i in range(response[DNS].ancount):
                print(f"    -> Answer: {response[DNS].an[i].rdata}")
        else:
            print("[-] No direct answers returned in the payload (Potential Recursive/Caching behavior).")
    else:
        print("[-] No response or port filtered.")

if __name__ == "__main__":
    test_dns_vulnerability("172.18.0.1", "y.net")
