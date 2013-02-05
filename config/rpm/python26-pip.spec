Name:		python26-pip
Version:	1.2.1
Release:	1%{?dist}
Summary:        python pip	

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	pip-1.2.1.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q -n pip-1.2.1


%build

%install
rm -rf %{buildroot}
python setup.py install --root %{buildroot}

%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/bin/pip
/usr/bin/pip-2.6
/usr/lib/python2.6/site-packages/pip-1.2.1-py2.6.egg-info/PKG-INFO
/usr/lib/python2.6/site-packages/pip-1.2.1-py2.6.egg-info/SOURCES.txt
/usr/lib/python2.6/site-packages/pip-1.2.1-py2.6.egg-info/dependency_links.txt
/usr/lib/python2.6/site-packages/pip-1.2.1-py2.6.egg-info/entry_points.txt
/usr/lib/python2.6/site-packages/pip-1.2.1-py2.6.egg-info/not-zip-safe
/usr/lib/python2.6/site-packages/pip-1.2.1-py2.6.egg-info/top_level.txt
/usr/lib/python2.6/site-packages/pip/__init__.py
/usr/lib/python2.6/site-packages/pip/__init__.pyc
/usr/lib/python2.6/site-packages/pip/__main__.py
/usr/lib/python2.6/site-packages/pip/__main__.pyc
/usr/lib/python2.6/site-packages/pip/backwardcompat.py
/usr/lib/python2.6/site-packages/pip/backwardcompat.pyc
/usr/lib/python2.6/site-packages/pip/basecommand.py
/usr/lib/python2.6/site-packages/pip/basecommand.pyc
/usr/lib/python2.6/site-packages/pip/baseparser.py
/usr/lib/python2.6/site-packages/pip/baseparser.pyc
/usr/lib/python2.6/site-packages/pip/commands/__init__.py
/usr/lib/python2.6/site-packages/pip/commands/__init__.pyc
/usr/lib/python2.6/site-packages/pip/commands/bundle.py
/usr/lib/python2.6/site-packages/pip/commands/bundle.pyc
/usr/lib/python2.6/site-packages/pip/commands/completion.py
/usr/lib/python2.6/site-packages/pip/commands/completion.pyc
/usr/lib/python2.6/site-packages/pip/commands/freeze.py
/usr/lib/python2.6/site-packages/pip/commands/freeze.pyc
/usr/lib/python2.6/site-packages/pip/commands/help.py
/usr/lib/python2.6/site-packages/pip/commands/help.pyc
/usr/lib/python2.6/site-packages/pip/commands/install.py
/usr/lib/python2.6/site-packages/pip/commands/install.pyc
/usr/lib/python2.6/site-packages/pip/commands/search.py
/usr/lib/python2.6/site-packages/pip/commands/search.pyc
/usr/lib/python2.6/site-packages/pip/commands/uninstall.py
/usr/lib/python2.6/site-packages/pip/commands/uninstall.pyc
/usr/lib/python2.6/site-packages/pip/commands/unzip.py
/usr/lib/python2.6/site-packages/pip/commands/unzip.pyc
/usr/lib/python2.6/site-packages/pip/commands/zip.py
/usr/lib/python2.6/site-packages/pip/commands/zip.pyc
/usr/lib/python2.6/site-packages/pip/download.py
/usr/lib/python2.6/site-packages/pip/download.pyc
/usr/lib/python2.6/site-packages/pip/exceptions.py
/usr/lib/python2.6/site-packages/pip/exceptions.pyc
/usr/lib/python2.6/site-packages/pip/index.py
/usr/lib/python2.6/site-packages/pip/index.pyc
/usr/lib/python2.6/site-packages/pip/locations.py
/usr/lib/python2.6/site-packages/pip/locations.pyc
/usr/lib/python2.6/site-packages/pip/log.py
/usr/lib/python2.6/site-packages/pip/log.pyc
/usr/lib/python2.6/site-packages/pip/req.py
/usr/lib/python2.6/site-packages/pip/req.pyc
/usr/lib/python2.6/site-packages/pip/runner.py
/usr/lib/python2.6/site-packages/pip/runner.pyc
/usr/lib/python2.6/site-packages/pip/status_codes.py
/usr/lib/python2.6/site-packages/pip/status_codes.pyc
/usr/lib/python2.6/site-packages/pip/util.py
/usr/lib/python2.6/site-packages/pip/util.pyc
/usr/lib/python2.6/site-packages/pip/vcs/__init__.py
/usr/lib/python2.6/site-packages/pip/vcs/__init__.pyc
/usr/lib/python2.6/site-packages/pip/vcs/bazaar.py
/usr/lib/python2.6/site-packages/pip/vcs/bazaar.pyc
/usr/lib/python2.6/site-packages/pip/vcs/git.py
/usr/lib/python2.6/site-packages/pip/vcs/git.pyc
/usr/lib/python2.6/site-packages/pip/vcs/mercurial.py
/usr/lib/python2.6/site-packages/pip/vcs/mercurial.pyc
/usr/lib/python2.6/site-packages/pip/vcs/subversion.py
/usr/lib/python2.6/site-packages/pip/vcs/subversion.pyc

%changelog

