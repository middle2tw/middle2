Name:		memcached-sasl
Version:	1.4.15
Release:	1%{?dist}
Summary:	Memcached with sasl

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	memcached-1.4.15.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q -n memcached-1.4.15

%build
%configure --enable-sasl
make

%install
make install DESTDIR=%{buildroot}

%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/bin/memcached
/usr/include/memcached/protocol_binary.h
/usr/share/man/man1/memcached.1.gz

%changelog

