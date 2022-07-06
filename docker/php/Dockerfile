FROM php:8.1-fpm-alpine

RUN apk update
# composer install
ENV COMPOSER_HOME /composer
ENV PATH ./vendor/bin:/composer/vendor/bin:$PATH
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer
# SSH install
ARG USER=root
ARG PASSWORD=root

RUN apk add openssh && \
    sed -i s/#PermitRootLogin.*/PermitRootLogin\ yes/ /etc/ssh/sshd_config && \
    echo "${USER}:${PASSWORD}" | chpasswd && \
    ssh-keygen -A
# PHP memory limit increasing
RUN echo 'memory_limit = 1024M' >> /usr/local/etc/php/conf.d/docker-php-ram-limit.ini
# RUN php and SSH
RUN printf "php-fpm -D\n/usr/sbin/sshd -D" >> /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
