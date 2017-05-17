#!/bin/bash

cd /var/www/html/
git clone https://www.github.com/orion40/openbee
sudo apt-get install motion

mkdir /home/pi/.motion/motion.conf

echo "stream_maxrate 30" >> .motion/motion.conf
echo "stream_port 8081" >> .motion/motion.conf
echo "stream_localhost off" >> .motion/motion.conf
echo "output_pictures off" >> .motion/motion.conf


