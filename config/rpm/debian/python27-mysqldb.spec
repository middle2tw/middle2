Name:	        MySQL-python
Version:	1.2.4
Release:	1%{?dist}
Summary:	for Python 2.7

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
# https://pypi.python.org/packages/source/M/MySQL-python/MySQL-python-1.2.4.zip#md5=ddf2386daf10a97af115ffad2ed4a9a0
Source0:	MySQL-python-1.2.4.zip
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
/usr/local/lib/python2.7/site-packages/MySQL_python-1.2.4-py2.7.egg-info/PKG-INFO
/usr/local/lib/python2.7/site-packages/MySQL_python-1.2.4-py2.7.egg-info/SOURCES.txt
/usr/local/lib/python2.7/site-packages/MySQL_python-1.2.4-py2.7.egg-info/dependency_links.txt
/usr/local/lib/python2.7/site-packages/MySQL_python-1.2.4-py2.7.egg-info/top_level.txt
/usr/local/lib/python2.7/site-packages/MySQLdb/__init__.py
/usr/local/lib/python2.7/site-packages/MySQLdb/__init__.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/connections.py
/usr/local/lib/python2.7/site-packages/MySQLdb/connections.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/CLIENT.py
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/CLIENT.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/CR.py
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/CR.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/ER.py
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/ER.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/FIELD_TYPE.py
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/FIELD_TYPE.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/FLAG.py
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/FLAG.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/REFRESH.py
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/REFRESH.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/__init__.py
/usr/local/lib/python2.7/site-packages/MySQLdb/constants/__init__.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/converters.py
/usr/local/lib/python2.7/site-packages/MySQLdb/converters.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/cursors.py
/usr/local/lib/python2.7/site-packages/MySQLdb/cursors.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/release.py
/usr/local/lib/python2.7/site-packages/MySQLdb/release.pyc
/usr/local/lib/python2.7/site-packages/MySQLdb/times.py
/usr/local/lib/python2.7/site-packages/MySQLdb/times.pyc
/usr/local/lib/python2.7/site-packages/_mysql.so
/usr/local/lib/python2.7/site-packages/_mysql_exceptions.py
/usr/local/lib/python2.7/site-packages/_mysql_exceptions.pyc

%changelog
