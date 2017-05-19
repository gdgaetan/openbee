#!/bin/bash

if [ "$EUID" -ne 0 ] then
    echo "[-] Vous avez besoin d'être root pour exécuter ce script.";
    exit;
fi

MOTION_DIR=/etc/motion
WWW_DIR=/var/www/html

echo "[*] Décompression du projet dans $WWW_DIR ..."
unzip projet.zip $WWW_DIR
cd $WWW_DIR
mysql < onebee.sql

apt-get update
apt-get install -y apache2 php5 mysql-server-5.5 motion

mkdir $MOTION_DIR

echo "stream_maxrate 30" >> $MOTION_DIR/motion.conf
echo "stream_port 8081" >> $MOTION_DIR/motion.conf
echo "stream_localhost off" >> $MOTION_DIR/motion.conf
echo "output_pictures off" >> $MOTION_DIR/motion.conf


echo "[+] Installation terminée. Pensez à lancer motion pour avoir le flux vidéo."
