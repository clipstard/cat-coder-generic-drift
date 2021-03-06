FROM php:7.3-fpm-stretch
MAINTAINER Patrick Coustans <patrick@tekoway.com>

ENV DEBIAN_FRONTEND noninteractive

# ------------------------------
#Xdebug env variables
# ------------------------------
ARG XDEBUG_REMOTE_HOST
ARG XDEBUG_REMOTE_PORT
ARG XDEBUG_IDE_KEY
ENV XDEBUG_REMOTE_HOST ${XDEBUG_REMOTE_HOST}
ENV XDEBUG_REMOTE_PORT ${XDEBUG_REMOTE_PORT}
ENV XDEBUG_IDE_KEY ${XDEBUG_IDE_KEY}

# ------------------------------
# Install xDebug, if enabled
# ------------------------------
ARG INSTALL_XDEBUG
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    # Install the xdebug extension
    pecl install xdebug && \
    docker-php-ext-enable xdebug \
    ;fi
# Copy xdebug configration for remote debugging
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# ------------------------------
# Add filters for documentation
# ------------------------------
COPY dpkg_nodoc /etc/dpkg/dpkg.cfg.d/01_nodoc
COPY dpkg_nolocales /etc/dpkg/dpkg.cfg.d/01_nolocales
COPY apt_nocache /etc/apt/apt.conf.d/02_nocache
COPY remove_doc.sh /usr/local/bin/remove_doc

# ------------------------------
# add some apt tweaks
# ------------------------------
COPY apt_speedup /etc/dpkg/dpkg.cfg.d/02_apt-speedup


# ------------------------------
#for pdf generator
#RUN apt-get install -y \
#libxrender1 \
#libfontconfig1 \
#libx11-dev \
#libjpeg62 \
#libxtst6 \
#wget/ \
#&& wget https://github.com/h4cc/wkhtmltopdf-amd64/blob/master/bin/wkhtmltopdf-amd64?raw=true -O /usr/local/bin/wkhtmltopdf \
#&& chmod +x /usr/local/bin/wkhtmltopdf \



# ------------------------------
# install usefull binaries
# ------------------------------
RUN apt-get -yqq update && \
    apt-get install -yqq --no-install-recommends git imagemagick libmagickwand-dev openssh-client \
                                                 unzip libpcre3 libfcgi0ldbl libmemcached-dev \
                                                 libpcre3-dev libssl-dev \
                                                 jq vim zip libzip-dev \
                                                 wget curl gnupg sudo libfreetype6-dev

# ------------------------------
# install PHP extensions
# ------------------------------
RUN docker-php-ext-install exif intl opcache mbstring pdo_mysql mysqli bcmath && \
    # docker-php-ext-install soap && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install -j$(nproc) gd && \
    docker-php-ext-configure zip --with-libzip && docker-php-ext-install zip && \
    # imagick
    echo '' | pecl install imagick && \
    echo "extension=imagick.so" > /usr/local/etc/php/conf.d/ext-imagick.ini && \
    # redis
    pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

ENV current_os=linux
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") && \
    curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/$current_os/amd64/$version && \
    mkdir -p /tmp/blackfire && \
    tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire && \
    mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so && \
    printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini && \
    rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

# ------------------------------
# user
# ------------------------------
ARG UID
ARG GID
RUN groupadd -g ${GID} app
RUN useradd -d /home/app -s /bin/bash -u ${UID} -g ${GID} app
RUN mkdir /home/app
RUN chown -R app:app /home/app

# ------------------------------
# health monitoring
# ------------------------------
RUN sed -i -e 's/^;ping\.path/ping\.path/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i -e 's/^;pm\.status_path/pm\.status_path/' /usr/local/etc/php-fpm.d/www.conf

COPY php-fpm-ping.sh /usr/local/bin/php-fpm-ping
COPY php-fpm-status.sh /usr/local/bin/php-fpm-status

RUN chmod a+x /usr/local/bin/php-fpm-ping
RUN chmod a+x /usr/local/bin/php-fpm-status

HEALTHCHECK --interval=1m --timeout=3s CMD /usr/local/bin/php-fpm-ping

# Include the start script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# ------------------------------
# cleaning
# ------------------------------
RUN apt-get remove -yqq --purge libpcre3-dev libssl-dev && \
    apt-get clean -yqq && rm -rf /tmp/* /var/lib/apt/cache/* && \
    bash /usr/local/bin/remove_doc && \
    rm -rf /root/*

# ------------------------------
# Install composer
# ------------------------------
WORKDIR /tmp

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm -rf /tmp/*

# ------------------------------
# Install blackfire
# ------------------------------
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm -rf /tmp/*



USER app

# ------------------------------
# Specific user commands
# ------------------------------
#RUN composer global require hirak/prestissimo

USER root

# Include the config files
COPY symfony.ini /usr/local/etc/php/conf.d/
COPY symfony.pool.conf /usr/local/etc/php-fpm.d/
COPY alias.sh /etc/profile.d/

CMD ["start.sh"]
