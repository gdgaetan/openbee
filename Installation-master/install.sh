#!/bin/bash

#installation des paquets principaux

#MAJ de la rpi
echo "  "
echo "Maj de la Raspberry"
echo "  "
sudo apt-get update
sudo apt-get upgrade --assume-yes


#installation de  apache
echo "  "
echo "Installation d'apache"
echo "  "
sudo apt-get install apache2 --assume-yes

#installation de l'extension php
echo "  "
echo "Installation de php5"
echo "  "
sudo apt-get install php5 --assume-yes

#installation d'une librairie complémentaire
echo "  "
echo "Installation de mysql pour php5"
echo "  "
sudo apt-get install mysql-server php5-mysql --assume-yes

#installation de la librairie de l'affichage des fenetres
echo "  "
echo "Installation de GTK"
echo "  "
sudo apt-get install gtk2.0 --assume-yes

#installation du client sql
echo "  "
echo "Installation du client sql"
echo "  "
sudo apt-get install libmysqlclient.dev --assume-yes

#installation de la librairie openCV
echo "  "
echo "Installation d'openCV"
echo "  "
sudo apt-get install libopencv-highgui-dev libopencv-dev --assume-yes

#installation de phpmyadmin
echo "  "
echo "installation de phpmyadmin"
echo "  "
sudo apt-get install phpmyadmin --assume-yes

#installation le bibliotheque Pigpio
echo "  "
echo "installation de PigPio"
echo "  "
rm pigpio.zip
sudo rm -rf PIGPIO
wget abyz.co.uk/rpi/pigpio/pigpio.zip
unzip pigpio.zip
cd PIGPIO
make -j4
sudo make install

#installation des paquets pour le wifi
echo "  "
echo "Installation de dnsmasq"
echo "  "
sudo apt-get install dnsmasq --assume-yes
echo "  "
echo "Installation de hostapd"
echo "  "
sudo apt-get install hostapd --assume-yes

#autorisation d'acces aux fichiers d'apache
echo "  "
echo "Création des répertoires ..."
echo "  "
sudo chown -R pi:www-data /var/www/
echo "..."
sudo chmod -R 770 /var/www/

#création de nos différents répertoires 

mkdir /var/www/Sauvegarde
echo "..."
mkdir /var/www/Sauvegarde/Fichier_csv
echo "..."
mkdir /var/www/Sauvegarde/Graphiques
echo "  "
echo "Création terminée"
echo "  "
#initialisation de la wifi
echo "  "
echo "Initialisation de la wifi"
echo "  "
sudo mv /etc/dhcpcd.conf /etc/dhcpcd.conf.orig

#ecriture dans le fichier
sudo cat > /etc/dhcpcd.conf <<- "EOF"
# A sample configuration for dhcpcd.
# See dhcpcd.conf(5) for details.

# Allow users of this group to interact with dhcpcd via the control socket.
#controlgroup wheel

# Inform the DHCP server of our hostname for DDNS.
hostname

# Use the hardware address of the interface for the Client ID.
clientid
# or
# Use the same DUID + IAID as set in DHCPv6 for DHCPv4 ClientID as per RFC4361.
#duid

# Persist interface configuration when dhcpcd exits.
persistent

# Rapid commit support.
# Safe to enable by default because it requires the equivalent option set
# on the server to actually work.
option rapid_commit

# A list of options to request from the DHCP server.
option domain_name_servers, domain_name, domain_search, host_name
option classless_static_routes
# Most distributions have NTP support.
option ntp_servers
# Respect the network MTU.
# Some interface drivers reset when changing the MTU so disabled by default.
#option interface_mtu

# A ServerID is required by RFC2131.
require dhcp_server_identifier

# Generate Stable Private IPv6 Addresses instead of hardware based ones
slaac private

# A hook script is provided to lookup the hostname if not set by the DHCP
# server, but it should not be run by default.
nohook lookup-hostname

denyinterface wlan0
EOF

#ecriture dans le fichier "interfaces"
echo "  "
echo "Initialisation de la plage IP"
echo "  "
sudo cat > /etc/network/interfaces <<-"EOF"
# interfaces(5) file used by ifup(8) and ifdown(8)

# Please note that this file is written to be used with dhcpcd
# For static IP, consult /etc/dhcpcd.conf and 'man dhcpcd.conf'

# Include files from /etc/network/interfaces.d:
source-directory /etc/network/interfaces.d

auto lo
iface lo inet loopback

iface eth0 inet manual

allow-hotplug wlan0
iface wlan0 inet static
address 10.0.0.1
netmask 255.255.255.0
network 10.0.0.0
broadcast 10.0.0.255
# wpa-conf /etc/wpa_supplicant/wpa_supplicant.conf

allow-hotplug wlan1
iface wlan1 inet manual
    wpa-conf /etc/wpa_supplicant/wpa_supplicant.conf

EOF

#redémarrage des services
echo "  "
echo "Redemarrage de dhcpcd..."
echo "..."
sudo service dhcpcd restart
sudo ifdown wlan0
sudo ifup wlan0

#ecriture dans le fichier hostapd -> configuration de la wifi
echo "  "
echo "Parametrage de la wifi"
echo "  "
sudo cat > /etc/hostapd/hostapd.conf <<-"EOF"
# This is the name of the WiFi interface we configured above
interface=wlan0
# Use the nl80211 driver with the brcmfmac driver
driver=nl80211
# This is the name of the network 
ssid=module-onebee1
# Use the 2.4GHz band
hw_mode=g
# Use channel 6
channel=6
# Enable 802.11n
ieee80211n=1
# Enable WMM
wmm_enabled=1
# Enable 40MHz channels with 20ns guard interval
ht_capab=[HT40][SHORT-GI-20][DSSS_CCK-40]
# Accept all MAC addresses
macaddr_acl=0
# Use WPA authentication
auth_algs=1
# Require clients to know the network name
ignore_broadcast_ssid=0
# Use WPA2
wpa=2
# Use a pre-shared key
wpa_key_mgmt=WPA-PSK
# The network passphrase 
wpa_passphrase=onebeemind
# Use AES, instead of TKIP
rsn_pairwise=CCMP
EOF

#ecriture dans le fichier hostapd
echo "  "
echo "mise en place du boot par au lancement de la rpi"
echo "  "
sudo cat > /etc/default/hostapd <<-"EOF"
# Defaults for hostapd initscript
#
# See /usr/share/doc/hostapd/README.Debian for information about alternative
# methods of managing hostapd.
#
# Uncomment and set DAEMON_CONF to the absolute path of a hostapd configuration
# file and hostapd will be started during system boot. An example configuration
# file can be found at /usr/share/doc/hostapd/examples/hostapd.conf.gz
#
DAEMON_CONF="/etc/hostapd/hostapd.conf"

# Additional daemon options to be appended to hostapd command:-
# 	-d   show more debug messages (-dd for even more)
# 	-K   include key data in debug messages
# 	-t   include timestamps in some debug messages
#
# Note that -B (daemon mode) and -P (pidfile) options are automatically
# configured by the init.d script and must not be added to DAEMON_OPTS.
#
#DAEMON_OPTS=""
EOF

#enregistrement du fichier source DNSMASQ en cas d'erreur
sudo mv /etc/dnsmasq.conf /etc/dnsmasq.conf.orig

#ecriture dans le fichier dnsmasq.conf
echo "  "
echo "Definition des parametre du dns"
echo "  "
sudo cat > /etc/dnsmasq.conf <<-"EOF"
interface=wlan0 # Use interface wlan0
listen-address=10.0.0.1 # Explicitly specify the address to listen on
bind-interfaces # Bind to the interface to make sure we aren't sending things elsewhere
server=8.8.8.8 # Forward DNS requests to Google DNS
domain-needed # Don't forward short names
bogus-priv # Never forward addresses in the non-routed address spaces.
dhcp-range=10.0.0.50,10.0.0.150,12h
EOF

#ecriture dans le fichier systcl.conf
echo "  "
echo "  "
sudo cat > /etc/sysctl.conf <<-"EOF"
#
# /etc/sysctl.conf - Configuration file for setting system variables
# See /etc/sysctl.d/ for additional system variables.
# See sysctl.conf (5) for information.
#

#kernel.domainname = example.com

# Uncomment the following to stop low-level messages on console
#kernel.printk = 3 4 1 3

##############################################################3
# Functions previously found in netbase
#

# Uncomment the next two lines to enable Spoof protection (reverse-path filter)
# Turn on Source Address Verification in all interfaces to
# prevent some spoofing attacks
#net.ipv4.conf.default.rp_filter=1
#net.ipv4.conf.all.rp_filter=1

# Uncomment the next line to enable TCP/IP SYN cookies
# See http://lwn.net/Articles/277146/
# Note: This may impact IPv6 TCP sessions too
#net.ipv4.tcp_syncookies=1

# Uncomment the next line to enable packet forwarding for IPv4
net.ipv4.ip_forward=1

# Uncomment the next line to enable packet forwarding for IPv6
#  Enabling this option disables Stateless Address Autoconfiguration
#  based on Router Advertisements for this host
#net.ipv6.conf.all.forwarding=1


###################################################################
# Additional settings - these settings can improve the network
# security of the host and prevent against some network attacks
# including spoofing attacks and man in the middle attacks through
# redirection. Some network environments, however, require that these
# settings are disabled so review and enable them as needed.
#
# Do not accept ICMP redirects (prevent MITM attacks)
#net.ipv4.conf.all.accept_redirects = 0
#net.ipv6.conf.all.accept_redirects = 0
# _or_
# Accept ICMP redirects only for gateways listed in our default
# gateway list (enabled by default)
# net.ipv4.conf.all.secure_redirects = 1
#
# Do not send ICMP redirects (we are not a router)
#net.ipv4.conf.all.send_redirects = 0
#
# Do not accept IP source route packets (we are not a router)
#net.ipv4.conf.all.accept_source_route = 0
#net.ipv6.conf.all.accept_source_route = 0
#
# Log Martian Packets
#net.ipv4.conf.all.log_martians = 1
#

EOF

#création des tables de routage
echo "  "
echo "Creationdes tables de routage"
echo "  "
sudo iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
sudo iptables -A FORWARD -i eth0 -o wlan0 -m state --state RELATED,ESTABLISHED -j ACCEPT
sudo iptables -A FORWARD -i wlan0 -o eth0 -j ACCEPT

#commande pour forcer le chargement des tables de routage au lancement
sudo sh -c "iptables-save > /etc/iptables.ipv4.nat"

#ecriture dans le fichier rc.local pour execution a la mise en route de la rpi
sudo cat > /etc/rc.local <<-"EOF"
#!/bin/sh -e
#
# rc.local
#
# This script is executed at the end of each multiuser runlevel.
# Make sure that the script will "exit 0" on success or any other
# value on error.
#
# In order to enable or disable this script just change the execution
# bits.
#
# By default this script does nothing.

# Print the IP address
_IP=$(hostname -I) || true
if [ "$_IP" ]; then
  printf "My IP address is %s\n" "$_IP"
fi

iptables-restore < /etc/iptables.ipv4.nat
sudo sh /home/pi/bin/Launch_on_start

exit 0

EOF

#execution et mise en marche des services
echo "  "
echo "Lancement des services"
echo "  "
sudo service hostapd start
sudo service dnsmasq start

cd 
cd /home/pi/Documents
mkdir ressource_download
cd ressource_download
wget  https://github.com/Smokey1602/Version-7---GTK---SQL---sauvegardeUSB---initialisation---threads/archive/master.zip
sudo unzip master.zip
sudo mv /home/pi/Documents/ressource_download/Version-7---GTK---SQL---sauvegardeUSB---initialisation---threads-master/ /home/pi/Documents/onebee

sudo chmod -R 777 /home/pi/Documents/onebee
echo "Compilation du programme"
cd /home/pi/Documents/onebee/
ls
sudo g++ -Wall -pthread -o onecounter camera.c -lpigpio -lrt -lwiringPi ` pkg-config --cflags --libs opencv gtk+-2.0` `mysql_config --cflags --libs`
sleep 2;
echo "Compilation terminée"
sleep 5;

#configuration pour le lancement automatique au démarrage
cd /home/pi/Desktop/
sudo wget https://raw.githubusercontent.com/Smokey1602/Installation/master/OneCounter
sudo chmod 777 OneCounter
cd .config/lxsession/LXDE-pi

sudo cat > autostart <<-"EOF"
@lxpanel --profile LXDE-pi
@pcmanfm --desktop --profile LXDE-pi
@xscreensaver -no-splash
@point-rpi
@./Desktop/OneCounter

EOF


clear 
echo "////////////////////////////////////////////////////////////////////////////////////////"
echo "///    Le module est maintenant fonctionnel. Il vous suffit d'effectuer une premiere ///"
echo "///   initialisation sur la fenetre qui va se lancer dans moins de 30secondes...     ///"
echo "///   A la fin de cette initialisation, le programme va se lancer.                   ///"
echo "///  Si des erreures sont apparues lors de l'installation merci de me les transmettre///"
echo "///                                                                                  ///"
echo "///                                              Realise par : Jean-Camille Lebreton ///"
echo "///                                                               Mind  Microtec     ///"
echo "////////////////////////////////////////////////////////////////////////////////////////"
echo "  "

sudo ./onecounter
