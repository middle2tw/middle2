Name:		python26-mysql
Version:	1.2.1
Release:	1%{?dist}
Summary:        python mysql

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	MySQL-python-1.2.4.zip
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q -n MySQL-python-1.2.4


%build

%install
rm -rf %{buildroot}
python setup.py install --root %{buildroot}

%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/lib64/python2.6/site-packages/MySQL_python-1.2.4-py2.6.egg-info/PKG-INFO
/usr/lib64/python2.6/site-packages/MySQL_python-1.2.4-py2.6.egg-info/SOURCES.txt
/usr/lib64/python2.6/site-packages/MySQL_python-1.2.4-py2.6.egg-info/dependency_links.txt
/usr/lib64/python2.6/site-packages/MySQL_python-1.2.4-py2.6.egg-info/top_level.txt
/usr/lib64/python2.6/site-packages/MySQLdb/__init__.py
/usr/lib64/python2.6/site-packages/MySQLdb/__init__.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/connections.py
/usr/lib64/python2.6/site-packages/MySQLdb/connections.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/constants/CLIENT.py
/usr/lib64/python2.6/site-packages/MySQLdb/constants/CLIENT.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/constants/CR.py
/usr/lib64/python2.6/site-packages/MySQLdb/constants/CR.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/constants/ER.py
/usr/lib64/python2.6/site-packages/MySQLdb/constants/ER.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/constants/FIELD_TYPE.py
/usr/lib64/python2.6/site-packages/MySQLdb/constants/FIELD_TYPE.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/constants/FLAG.py
/usr/lib64/python2.6/site-packages/MySQLdb/constants/FLAG.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/constants/REFRESH.py
/usr/lib64/python2.6/site-packages/MySQLdb/constants/REFRESH.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/constants/__init__.py
/usr/lib64/python2.6/site-packages/MySQLdb/constants/__init__.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/converters.py
/usr/lib64/python2.6/site-packages/MySQLdb/converters.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/cursors.py
/usr/lib64/python2.6/site-packages/MySQLdb/cursors.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/release.py
/usr/lib64/python2.6/site-packages/MySQLdb/release.pyc
/usr/lib64/python2.6/site-packages/MySQLdb/times.py
/usr/lib64/python2.6/site-packages/MySQLdb/times.pyc
/usr/lib64/python2.6/site-packages/_mysql.so
/usr/lib64/python2.6/site-packages/_mysql_exceptions.py
/usr/lib64/python2.6/site-packages/_mysql_exceptions.pyc


%changelog

