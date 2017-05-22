#!/bin/bash

MOTION_DIR="/etc/motion"
WWW_DIR="/var/www/html"
SOFTWARES="apache2 php5 mysql-server-5.5 motion php5-mysql"

if [ "$EUID" -ne 0 ]; then
    echo "[-] Vous avez besoin d'être root pour exécuter ce script.";
    exit;
fi

echo "[*] Installation des logiciels nécessaires au programme: $SOFTWARES"

apt-get update
apt-get install -y $SOFTWARES

echo "[*] Répertoire de configuration de motion: $MOTION_DIR"
echo "[*] Décompression du projet dans $WWW_DIR ..."
unzip projet.zip -d $WWW_DIR/
cd $WWW_DIR

echo "[*] Veuillez entrer le mot de passe root pour mysql."
mysql -u root -p < $WWW_DIR/onebee.sql

mkdir $MOTION_DIR -p

touch $MOTION_DIR/motion.conf
chown motion:motion $MOTION_DIR/
chown motion:motion $MOTION_DIR/motion.conf
chmod 755 $MOTION_DIR/motion.conf
echo "stream_maxrate 30" > $MOTION_DIR/motion.conf
echo "stream_port 8081" >> $MOTION_DIR/motion.conf
echo "stream_localhost off" >> $MOTION_DIR/motion.conf
echo "output_pictures off" >> $MOTION_DIR/motion.conf

echo "[*] Redémarrage d'apache2..."
# Nécessaire pour prendre en compte l'installation de PDO pour mysql
service apache2 restart;

echo "[+] Installation terminée. Pensez à lancer motion pour avoir le flux vidéo."
