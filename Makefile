INSTALLDIR=/var/www/html/$(DESTDIR)homelog


FILES= ajaxserver.php connect_db.php dbconn.php  last_voltage.php  maintenance.php index.php last.php  list.php stripchart.php ajax-bar.gif chart.js  homelog.js  stripchart.js stripchart.svg handheld.css  msi_smarty.css  print.css  svgstyle.css  tempdata.css

TEMPLATEDIR=templates
SUBDIRS = cache templates_c $(TEMPLATEDIR) 
TEMPLATES=last.tpl last_voltages.tpl list.tpl maintenance.tpl page_foot.tpl page_head_noupdate.tpl page_head.tpl  stripchart.tpl


.PHONY: install
install:
	mkdir -p $(INSTALLDIR)
	for file in $(FILES); do \
	  install -m 0644 -o www-data $$file $(INSTALLDIR) ; \
	done
	for dir in $(SUBDIRS); do \
	   mkdir -p $(INSTALLDIR)/$$dir ; \
	   chown www-data $(INSTALLDIR)/$$dir; \
	done
	for file in $(TEMPLATES); do \
	  install -m 0644 -o www-data $(TEMPLATEDIR)/$$file $(INSTALLDIR)/$(TEMPLATEDIR) ; \
	done

