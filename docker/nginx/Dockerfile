FROM nginx:1.17

RUN  apt-get update && apt-get install apt-file -y && apt-file update && apt-get install vim -y
#RUN update-rc.d cron enable
#ADD start.sh /usr/local/bin/
#RUN chmod a+x /usr/local/bin/start.sh
ADD nginx.conf /etc/nginx/
ADD default.conf /etc/nginx/sites-enabled/
ADD symfony.conf /etc/nginx/sites-enabled/
#ADD reverse.conf /etc/nginx/conf.d/

#CMD ["/usr/local/bin/start.sh"]

EXPOSE 80
EXPOSE 443

# ------------------------------
# user
# ------------------------------
ARG UID
ARG GID
RUN groupadd -g ${GID} app
RUN useradd -d /home/app -s /bin/bash -u ${UID} -g ${GID} app
RUN mkdir /home/app
RUN chown -R app:app /home/app
RUN touch /var/run/nginx.pid \
 && chown -R app:app /var/run/nginx.pid /var/cache/nginx
