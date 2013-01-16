Name:		thrift-fb303
Version:	0.9.0
Release:	1%{?dist}
Summary:	thrift fb303 contrib

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	thrift-0.9.0.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q -n thrift-0.9.0


%build
cd contrib/fb303
./bootstrap.sh
%configure CPPFLAGS="-DHAVE_INTTYPES_H -DHAVE_NETINET_IN_H" --with-thriftpath=/usr/
make

%install
rm -rf %{buildroot}
cd contrib/fb303
make install DESTDIR=%{buildroot}


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/include/thrift/fb303/FacebookBase.h
/usr/include/thrift/fb303/FacebookService.h
/usr/include/thrift/fb303/ServiceTracker.h
/usr/include/thrift/fb303/fb303_constants.h
/usr/include/thrift/fb303/fb303_types.h
/usr/lib/python2.6/site-packages/fb303/FacebookBase.py
/usr/lib/python2.6/site-packages/fb303/FacebookBase.pyc
/usr/lib/python2.6/site-packages/fb303/FacebookService.py
/usr/lib/python2.6/site-packages/fb303/FacebookService.pyc
/usr/lib/python2.6/site-packages/fb303/__init__.py
/usr/lib/python2.6/site-packages/fb303/__init__.pyc
/usr/lib/python2.6/site-packages/fb303/constants.py
/usr/lib/python2.6/site-packages/fb303/constants.pyc
/usr/lib/python2.6/site-packages/fb303/ttypes.py
/usr/lib/python2.6/site-packages/fb303/ttypes.pyc
/usr/lib/python2.6/site-packages/fb303_scripts/__init__.py
/usr/lib/python2.6/site-packages/fb303_scripts/__init__.pyc
/usr/lib/python2.6/site-packages/fb303_scripts/fb303_simple_mgmt.py
/usr/lib/python2.6/site-packages/fb303_scripts/fb303_simple_mgmt.pyc
/usr/lib/python2.6/site-packages/thrift-0.9.0-py2.6.egg-info/PKG-INFO
/usr/lib/python2.6/site-packages/thrift-0.9.0-py2.6.egg-info/SOURCES.txt
/usr/lib/python2.6/site-packages/thrift-0.9.0-py2.6.egg-info/dependency_links.txt
/usr/lib/python2.6/site-packages/thrift-0.9.0-py2.6.egg-info/top_level.txt
/usr/lib64/libfb303.a
/usr/share/fb303/if/fb303.thrift

%changelog

