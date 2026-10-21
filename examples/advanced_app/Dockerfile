# EspoCRM 10.0.10 is the current stable release. The official image runs the installer on first start, and its
# custom/, data/ and client/custom/ directories are volumes, which is the layout the extension install needs.
# guard-core.zip is the extension built by `make example-zip` (npm run extension) and is not committed.
FROM espocrm/espocrm:10.0.10-apache

COPY guard-core.zip /opt/guard/guard-core.zip
COPY guard-entrypoint.sh guard-setup.sh apply-config.php guard-config.php /opt/guard/
RUN chmod +x /opt/guard/guard-entrypoint.sh /opt/guard/guard-setup.sh \
    && sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

# Apache runs as www-data (uid 33), which cannot bind port 80, so it listens on 8080.
USER 33

EXPOSE 8080

# Any HTTP answer from Apache counts as alive; the compose health check also waits for the guard setup.
HEALTHCHECK --interval=10s --timeout=5s --start-period=60s --retries=10 \
    CMD ["curl", "-s", "-o", "/dev/null", "http://127.0.0.1:8080/"]

ENTRYPOINT ["/opt/guard/guard-entrypoint.sh"]
CMD ["apache2-foreground"]
