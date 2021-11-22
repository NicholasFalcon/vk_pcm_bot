FROM php:7.4-cli

RUN apt-get update && apt-get install -y locales && rm -rf /var/lib/apt/lists/* && localedef -i ru_RU -c -f UTF-8 -A /usr/share/locale/locale.alias ru_RU.UTF-8

RUN pecl install xdebug-2.8.1 \
    && apt-get update && apt-get install -y libpq-dev git \
    && docker-php-ext-install mysqli pdo pdo_mysql pcntl \
    && docker-php-ext-enable xdebug mysqli pdo pcntl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /opt/pcm_bot
ENTRYPOINT ["php", "pcm_start.php"]
