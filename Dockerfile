# Use an official lightweight PHP image with Apache built-in
FROM php:8.2-apache

# Install the PDO MySQL extension so PHP can talk to the MySQL container
RUN docker-php-ext-install pdo pdo_mysql

# Install msmtp so PHP's mail() can send to the Mailpit container
RUN apt-get update && apt-get install -y msmtp msmtp-mta \
    && rm -rf /var/lib/apt/lists/*
RUN printf 'account default\nhost mail\nport 1025\nfrom system-noreply@kabarak.ac.ke\n' > /etc/msmtprc
RUN echo 'sendmail_path = "/usr/bin/msmtp -t"' > /usr/local/etc/php/conf.d/mail.ini

# Copy your local PHP/HTML/CSS files into the container's web directory
COPY . /var/www/html/

# Open up port 80 for web traffic
EXPOSE 80