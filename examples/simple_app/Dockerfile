# EspoCRM 10.0.10 is the current stable release. The official image runs the installer on first start, and its
# custom/, data/ and client/custom/ directories are volumes, which is the layout the extension install needs.
# guard-core.zip is the extension built by `make example-zip` (npm run extension) and is not committed.
FROM espocrm/espocrm:10.0.10-apache

COPY guard-core.zip /opt/guard/guard-core.zip
COPY guard-entrypoint.sh guard-setup.sh apply-config.php guard-config.php /opt/guard/
RUN chmod +x /opt/guard/guard-entrypoint.sh /opt/guard/guard-setup.sh

ENTRYPOINT ["/opt/guard/guard-entrypoint.sh"]
CMD ["apache2-foreground"]
