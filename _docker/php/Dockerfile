FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    vim \
    git \
    curl \
    wget \
    cron \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install gd bcmath ctype exif mbstring pdo_mysql pcntl

#Install ext-zip
RUN apt-get update && \
     apt-get install -y \
         libzip-dev \
         && docker-php-ext-install zip

# Install Redis
RUN pecl install redis && docker-php-ext-enable redis

# Get Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.8.10

#PHP 8.4 GD ext Config
RUN apt-get update && apt-get install -y \
        libjpeg-dev \
    	libjpeg62-turbo-dev \
    	libpng-dev \
    	libfreetype6-dev \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

#Python modules and pip
RUN apt-get install -y python3-pip python3-fitz python3-pandas python3-venv

#GMP for sendgrid
RUN apt-get install -y libgmp-dev -y && docker-php-ext-install gmp

# Install Node.js
RUN curl -sL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install -y nodejs
RUN export NODE_OPTIONS=--openssl-legacy-provider

#PHP Imagick
RUN apt-get update && apt-get install -y \
    imagemagick \
    libmagickwand-dev \
    && git clone https://github.com/Imagick/imagick --depth 1 /tmp/imagick \
    && cd /tmp/imagick \
    && phpize && ./configure \
    && make \
    && make install \
    && apt-get clean

RUN docker-php-ext-enable imagick

#Python global packages
RUN pip install --break-system-packages pdfplumber
RUN pip install --break-system-packages mkdocs

#Php Settings
ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

# Add cron task for runing Laravel schedule
RUN touch /var/log/cron.log \
    && echo "* * * * * root php /var/www/main/artisan schedule:run >> /var/log/cron.log 2>&1" >> /etc/crontab

# Copy supervisor configs
COPY ./laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# Arguments defined in docker-compose.yml
ARG user

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

#cannot use not-root user for Cron
#USER $user

EXPOSE 9000

CMD bash -c "cron && supervisord && php-fpm"