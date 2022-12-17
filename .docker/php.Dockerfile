FROM php:8.1-cli-alpine

ARG swoole_ver

ENV SWOOLE_VER=${swoole_ver:-"v4.8.7"}

#install some utilities
RUN set -ex \
    && cd /tmp \
    && sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories \
    && apk update \
    && apk add vim git autoconf openssl-dev build-base zlib-dev re2c libpng-dev oniguruma-dev linux-headers

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


#install php ext
RUN php -m \
    && docker-php-ext-install gd pdo pdo_mysql mysqli sockets pcntl \
    && php -m

#install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# install swoole
RUN cd /tmp \
    # from mirrors
    && git clone https://gitee.com/swoole/swoole swoole \
    && cd swoole \
    #Switch to the specified version of tag
    && git checkout ${SWOOLE_VER} \
    && phpize \
    #Execute the configure command
    && ./configure --enable-openssl --enable-sockets --enable-http2 --enable-mysqlnd \
    && make \
    && make install \
    #The extension is enabled through docker PHP ext enable, which is also provided by PHP.
    && docker-php-ext-enable swoole \
    #Check the modules PHP has installed
    && php -m \
    #Check that swoole is installed correctly
    && php --ri swoole

# config php
RUN cd /usr/local/etc/php/conf.d \
    # swoole config
    #To turn off the short name of swoole, it is necessary to use hyperf
    && echo "swoole.use_shortname = off" >> 99-off-swoole-shortname.ini \
    # config xdebug
    && { \
        #Add an Xdebug node
        echo "[Xdebug]"; \
        echo "xdebug.mode = debug"; \
        #This is a multi person debugging, but now it's a little difficult, so I won't start it for the time being
        echo ";xdebug.remote_connect_back = On"; \
        echo "xdebug.start_with_request  = yes"; \
        #Here, the host can fill in the IP address taken previously, or fill in the IP address host.docker.internal  。
        echo "xdebug.client_host = host.docker.internal"; \
        #Here the port is fixed to fill in 19000. Of course, other ports can be filled in. It needs to be ensured that they are not occupied
        echo "xdebug.client_port = 19000"; \
        #Fixed here
        echo "xdebug.idekey=PHPSTORM"; \
        #Save execution results to 99 Xdebug- enable.ini  Go inside
    } | tee 99-xdebug-enable.ini

# check
#Check PHP version information and installed modules
RUN cd /tmp \
    #Check PHP version
    && php -v \
    #Check installed modules
    && php -m \
    && echo -e "Build Completed!"

#Exposed 9501 port
EXPOSE 9501

WORKDIR /opt/pcm_bot

ENTRYPOINT ["php", "pcm_start.php"]
