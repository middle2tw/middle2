Name:	        pip
Version:	1.3.1
Release:	1%{?dist}
Summary:	pip for Python 2.7

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
# https://pypi.python.org/packages/source/p/pip/pip-1.3.1.tar.gz
Source0:	pip-1.3.1.tar.gz
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
/usr/bin/pip
/usr/bin/pip-2.7
/usr/lib/python2.7/site-packages/pip-1.3.1-py2.7.egg-info/PKG-INFO
/usr/lib/python2.7/site-packages/pip-1.3.1-py2.7.egg-info/SOURCES.txt
/usr/lib/python2.7/site-packages/pip-1.3.1-py2.7.egg-info/dependency_links.txt
/usr/lib/python2.7/site-packages/pip-1.3.1-py2.7.egg-info/entry_points.txt
/usr/lib/python2.7/site-packages/pip-1.3.1-py2.7.egg-info/not-zip-safe
/usr/lib/python2.7/site-packages/pip-1.3.1-py2.7.egg-info/requires.txt
/usr/lib/python2.7/site-packages/pip-1.3.1-py2.7.egg-info/top_level.txt
/usr/lib/python2.7/site-packages/pip/__init__.py
/usr/lib/python2.7/site-packages/pip/__init__.pyc
/usr/lib/python2.7/site-packages/pip/__main__.py
/usr/lib/python2.7/site-packages/pip/__main__.pyc
/usr/lib/python2.7/site-packages/pip/backwardcompat/__init__.py
/usr/lib/python2.7/site-packages/pip/backwardcompat/__init__.pyc
/usr/lib/python2.7/site-packages/pip/backwardcompat/socket_create_connection.py
/usr/lib/python2.7/site-packages/pip/backwardcompat/socket_create_connection.pyc
/usr/lib/python2.7/site-packages/pip/backwardcompat/ssl_match_hostname.py
/usr/lib/python2.7/site-packages/pip/backwardcompat/ssl_match_hostname.pyc
/usr/lib/python2.7/site-packages/pip/basecommand.py
/usr/lib/python2.7/site-packages/pip/basecommand.pyc
/usr/lib/python2.7/site-packages/pip/baseparser.py
/usr/lib/python2.7/site-packages/pip/baseparser.pyc
/usr/lib/python2.7/site-packages/pip/cacert.pem
/usr/lib/python2.7/site-packages/pip/cmdoptions.py
/usr/lib/python2.7/site-packages/pip/cmdoptions.pyc
/usr/lib/python2.7/site-packages/pip/commands/__init__.py
/usr/lib/python2.7/site-packages/pip/commands/__init__.pyc
/usr/lib/python2.7/site-packages/pip/commands/bundle.py
/usr/lib/python2.7/site-packages/pip/commands/bundle.pyc
/usr/lib/python2.7/site-packages/pip/commands/completion.py
/usr/lib/python2.7/site-packages/pip/commands/completion.pyc
/usr/lib/python2.7/site-packages/pip/commands/freeze.py
/usr/lib/python2.7/site-packages/pip/commands/freeze.pyc
/usr/lib/python2.7/site-packages/pip/commands/help.py
/usr/lib/python2.7/site-packages/pip/commands/help.pyc
/usr/lib/python2.7/site-packages/pip/commands/install.py
/usr/lib/python2.7/site-packages/pip/commands/install.pyc
/usr/lib/python2.7/site-packages/pip/commands/list.py
/usr/lib/python2.7/site-packages/pip/commands/list.pyc
/usr/lib/python2.7/site-packages/pip/commands/search.py
/usr/lib/python2.7/site-packages/pip/commands/search.pyc
/usr/lib/python2.7/site-packages/pip/commands/show.py
/usr/lib/python2.7/site-packages/pip/commands/show.pyc
/usr/lib/python2.7/site-packages/pip/commands/uninstall.py
/usr/lib/python2.7/site-packages/pip/commands/uninstall.pyc
/usr/lib/python2.7/site-packages/pip/commands/unzip.py
/usr/lib/python2.7/site-packages/pip/commands/unzip.pyc
/usr/lib/python2.7/site-packages/pip/commands/zip.py
/usr/lib/python2.7/site-packages/pip/commands/zip.pyc
/usr/lib/python2.7/site-packages/pip/download.py
/usr/lib/python2.7/site-packages/pip/download.pyc
/usr/lib/python2.7/site-packages/pip/exceptions.py
/usr/lib/python2.7/site-packages/pip/exceptions.pyc
/usr/lib/python2.7/site-packages/pip/index.py
/usr/lib/python2.7/site-packages/pip/index.pyc
/usr/lib/python2.7/site-packages/pip/locations.py
/usr/lib/python2.7/site-packages/pip/locations.pyc
/usr/lib/python2.7/site-packages/pip/log.py
/usr/lib/python2.7/site-packages/pip/log.pyc
/usr/lib/python2.7/site-packages/pip/req.py
/usr/lib/python2.7/site-packages/pip/req.pyc
/usr/lib/python2.7/site-packages/pip/runner.py
/usr/lib/python2.7/site-packages/pip/runner.pyc
/usr/lib/python2.7/site-packages/pip/status_codes.py
/usr/lib/python2.7/site-packages/pip/status_codes.pyc
/usr/lib/python2.7/site-packages/pip/util.py
/usr/lib/python2.7/site-packages/pip/util.pyc
/usr/lib/python2.7/site-packages/pip/vcs/__init__.py
/usr/lib/python2.7/site-packages/pip/vcs/__init__.pyc
/usr/lib/python2.7/site-packages/pip/vcs/bazaar.py
/usr/lib/python2.7/site-packages/pip/vcs/bazaar.pyc
/usr/lib/python2.7/site-packages/pip/vcs/git.py
/usr/lib/python2.7/site-packages/pip/vcs/git.pyc
/usr/lib/python2.7/site-packages/pip/vcs/mercurial.py
/usr/lib/python2.7/site-packages/pip/vcs/mercurial.pyc
/usr/lib/python2.7/site-packages/pip/vcs/subversion.py
/usr/lib/python2.7/site-packages/pip/vcs/subversion.pyc

%changelog
