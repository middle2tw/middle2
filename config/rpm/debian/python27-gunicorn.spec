Name:	        gunicorn
Version:	17.5
Release:	1%{?dist}
Summary:	gunicorn for Python 2.7

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
# https://pypi.python.org/packages/source/g/gunicorn/gunicorn-17.5.tar.gz
Source0:	gunicorn-17.5.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)
%description


%prep
%setup -q

%build


%install
python setup.py install --root %{buildroot}

%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/local/bin/gunicorn
/usr/local/bin/gunicorn_django
/usr/local/bin/gunicorn_paster
/usr/local/lib/python2.7/site-packages/gunicorn-17.5-py2.7.egg-info/PKG-INFO
/usr/local/lib/python2.7/site-packages/gunicorn-17.5-py2.7.egg-info/SOURCES.txt
/usr/local/lib/python2.7/site-packages/gunicorn-17.5-py2.7.egg-info/dependency_links.txt
/usr/local/lib/python2.7/site-packages/gunicorn-17.5-py2.7.egg-info/entry_points.txt
/usr/local/lib/python2.7/site-packages/gunicorn-17.5-py2.7.egg-info/not-zip-safe
/usr/local/lib/python2.7/site-packages/gunicorn-17.5-py2.7.egg-info/top_level.txt
/usr/local/lib/python2.7/site-packages/gunicorn/__init__.py
/usr/local/lib/python2.7/site-packages/gunicorn/__init__.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/app/__init__.py
/usr/local/lib/python2.7/site-packages/gunicorn/app/__init__.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/app/base.py
/usr/local/lib/python2.7/site-packages/gunicorn/app/base.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/app/django_wsgi.py
/usr/local/lib/python2.7/site-packages/gunicorn/app/django_wsgi.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/app/djangoapp.py
/usr/local/lib/python2.7/site-packages/gunicorn/app/djangoapp.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/app/pasterapp.py
/usr/local/lib/python2.7/site-packages/gunicorn/app/pasterapp.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/app/wsgiapp.py
/usr/local/lib/python2.7/site-packages/gunicorn/app/wsgiapp.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/arbiter.py
/usr/local/lib/python2.7/site-packages/gunicorn/arbiter.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/argparse_compat.py
/usr/local/lib/python2.7/site-packages/gunicorn/argparse_compat.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/config.py
/usr/local/lib/python2.7/site-packages/gunicorn/config.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/debug.py
/usr/local/lib/python2.7/site-packages/gunicorn/debug.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/errors.py
/usr/local/lib/python2.7/site-packages/gunicorn/errors.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/glogging.py
/usr/local/lib/python2.7/site-packages/gunicorn/glogging.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/http/__init__.py
/usr/local/lib/python2.7/site-packages/gunicorn/http/__init__.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/http/_sendfile.py
/usr/local/lib/python2.7/site-packages/gunicorn/http/_sendfile.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/http/body.py
/usr/local/lib/python2.7/site-packages/gunicorn/http/body.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/http/errors.py
/usr/local/lib/python2.7/site-packages/gunicorn/http/errors.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/http/message.py
/usr/local/lib/python2.7/site-packages/gunicorn/http/message.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/http/parser.py
/usr/local/lib/python2.7/site-packages/gunicorn/http/parser.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/http/unreader.py
/usr/local/lib/python2.7/site-packages/gunicorn/http/unreader.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/http/wsgi.py
/usr/local/lib/python2.7/site-packages/gunicorn/http/wsgi.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/management/__init__.py
/usr/local/lib/python2.7/site-packages/gunicorn/management/__init__.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/management/commands/__init__.py
/usr/local/lib/python2.7/site-packages/gunicorn/management/commands/__init__.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/management/commands/run_gunicorn.py
/usr/local/lib/python2.7/site-packages/gunicorn/management/commands/run_gunicorn.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/pidfile.py
/usr/local/lib/python2.7/site-packages/gunicorn/pidfile.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/six.py
/usr/local/lib/python2.7/site-packages/gunicorn/six.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/sock.py
/usr/local/lib/python2.7/site-packages/gunicorn/sock.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/util.py
/usr/local/lib/python2.7/site-packages/gunicorn/util.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/workers/__init__.py
/usr/local/lib/python2.7/site-packages/gunicorn/workers/__init__.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/workers/async.py
/usr/local/lib/python2.7/site-packages/gunicorn/workers/async.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/workers/base.py
/usr/local/lib/python2.7/site-packages/gunicorn/workers/base.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/workers/geventlet.py
/usr/local/lib/python2.7/site-packages/gunicorn/workers/geventlet.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/workers/ggevent.py
/usr/local/lib/python2.7/site-packages/gunicorn/workers/ggevent.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/workers/gtornado.py
/usr/local/lib/python2.7/site-packages/gunicorn/workers/gtornado.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/workers/sync.py
/usr/local/lib/python2.7/site-packages/gunicorn/workers/sync.pyc
/usr/local/lib/python2.7/site-packages/gunicorn/workers/workertmp.py
/usr/local/lib/python2.7/site-packages/gunicorn/workers/workertmp.pyc

%changelog
