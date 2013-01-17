Name:		scribe
Version:	2.0
Release:	1%{?dist}
Summary:	scribe 2.0

Group:		Hisoku
License:	no
URL:		http://hisoku.ronny.tw/
Source0:	scribe-2.0.zip
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q -n scribe-master


%build
./bootstrap.sh --with-boost-filesystem=boost_filesystem --with-boost-system=boost_system
%configure --with-boost-filesystem=boost_filesystem --with-boost-system=boost_system CPPFLAGS="-DHAVE_INTTYPES_H -DHAVE_NETINET_IN_H"
make


%install
rm -rf %{buildroot}
make install DESTDIR=%{buildroot}


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/bin/scribed
/usr/lib/python2.6/site-packages/scribe-2.0-py2.6.egg-info
/usr/lib/python2.6/site-packages/scribe/__init__.py
/usr/lib/python2.6/site-packages/scribe/__init__.pyc
/usr/lib/python2.6/site-packages/scribe/constants.py
/usr/lib/python2.6/site-packages/scribe/constants.pyc
/usr/lib/python2.6/site-packages/scribe/scribe.py
/usr/lib/python2.6/site-packages/scribe/scribe.pyc
/usr/lib/python2.6/site-packages/scribe/ttypes.py
/usr/lib/python2.6/site-packages/scribe/ttypes.pyc
/usr/lib64/libdynamicbucketupdater.a
/usr/lib64/libscribe.a

%changelog

