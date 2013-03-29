Name:		proj
Version:	4.8.0
Release:	1%{?dist}
Summary:	proj

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	proj-4.8.0.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q

%build
./configure 
make %{?_smp_mflags}


%install
rm -rf %{buildroot}
make install DESTDIR=%{buildroot}


%clean
rm -rf %{buildroot}

%files
%defattr(-,root,root,-)
%doc
/usr/local/bin/cs2cs
/usr/local/bin/geod
/usr/local/bin/invgeod
/usr/local/bin/invproj
/usr/local/bin/nad2bin
/usr/local/bin/proj
/usr/local/include/org_proj4_Projections.h
/usr/local/include/proj_api.h
/usr/local/lib/libproj.a
/usr/local/lib/libproj.la
/usr/local/lib/libproj.so
/usr/local/lib/libproj.so.0
/usr/local/lib/libproj.so.0.7.0
/usr/local/lib/pkgconfig/proj.pc
/usr/local/share/man/man1/cs2cs.1
/usr/local/share/man/man1/geod.1
/usr/local/share/man/man1/proj.1
/usr/local/share/man/man3/pj_init.3
/usr/local/share/proj/GL27
/usr/local/share/proj/IGNF
/usr/local/share/proj/epsg
/usr/local/share/proj/esri
/usr/local/share/proj/esri.extra
/usr/local/share/proj/nad.lst
/usr/local/share/proj/nad27
/usr/local/share/proj/nad83
/usr/local/share/proj/other.extra
/usr/local/share/proj/proj_def.dat
/usr/local/share/proj/world

%changelog

