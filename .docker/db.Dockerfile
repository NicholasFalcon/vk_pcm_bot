FROM mariadb

RUN apt-get update && apt-get install -y locales && rm -rf /var/lib/apt/lists/* && localedef -i ru_RU -c -f UTF-8 -A /usr/share/locale/locale.alias ru_RU.UTF-8

ENV MARIADB_ROOT_PASSWORD 1234
ENV MARIADB_USER admin
ENV MARIADB_PASSWORD 1234
ENV MARIADB_DATABASE pcm_bot
ENV LANG ru_RU.utf8

ADD .docker/pcm_bot.sql /docker-entrypoint-initdb.d
