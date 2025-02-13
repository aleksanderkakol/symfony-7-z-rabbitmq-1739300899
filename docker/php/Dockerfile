FROM php:8.3-fpm
ENV TIMEZONE=Europe/Warsaw

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y \
    openssl \
    libicu-dev \
    git \
    unzip \
    wget \
    libzip-dev \
    librabbitmq-dev \
    nodejs

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version


RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone \
    && printf '[Date]\ndate.timezone="%s"\n', $TIMEZONE > /usr/local/etc/php/conf.d/tzone.ini

RUN docker-php-ext-install pdo pdo_mysql zip intl opcache \
    && docker-php-ext-configure intl \
    && pecl install amqp \
    && docker-php-ext-enable amqp

RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && echo 'export PATH="$HOME/.symfony/bin:/var/www/bin:$PATH"' >> ~/.bashrc

WORKDIR /var/www
