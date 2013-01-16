Name:		php54-ext-common
Version:	5.4.10
Release:	1%{?dist}
Summary:	php54-ext-common

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	php-5.4.10.tar.bz2
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q -n php-5.4.10


%build
for EXT in ctype dom fileinfo filter hash iconv json pdo phar posix session simplexml tokenizer xml xmlreader xmlwriter; do
cd ext/${EXT}
phpize
%configure
make %{?_smp_mflags}
cd ../../
done


%install
rm -rf %{buildroot}
for EXT in ctype dom fileinfo filter hash iconv json pdo phar posix session simplexml tokenizer xml xmlreader xmlwriter; do
cd ext/${EXT}
make install INSTALL_ROOT=%{buildroot}
cd ../../
done


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/include/php/ext/dom/xml_common.h
/usr/include/php/ext/filter/php_filter.h
/usr/include/php/ext/hash/php_hash.h
/usr/include/php/ext/hash/php_hash_adler32.h
/usr/include/php/ext/hash/php_hash_crc32.h
/usr/include/php/ext/hash/php_hash_fnv.h
/usr/include/php/ext/hash/php_hash_gost.h
/usr/include/php/ext/hash/php_hash_haval.h
/usr/include/php/ext/hash/php_hash_joaat.h
/usr/include/php/ext/hash/php_hash_md.h
/usr/include/php/ext/hash/php_hash_ripemd.h
/usr/include/php/ext/hash/php_hash_sha.h
/usr/include/php/ext/hash/php_hash_snefru.h
/usr/include/php/ext/hash/php_hash_tiger.h
/usr/include/php/ext/hash/php_hash_types.h
/usr/include/php/ext/hash/php_hash_whirlpool.h
/usr/include/php/ext/iconv/config.h
/usr/include/php/ext/iconv/php_iconv.h
/usr/include/php/ext/json/php_json.h
/usr/include/php/ext/pdo/php_pdo.h
/usr/include/php/ext/pdo/php_pdo_driver.h
/usr/include/php/ext/session/mod_files.h
/usr/include/php/ext/session/mod_user.h
/usr/include/php/ext/session/php_session.h
/usr/include/php/ext/xml/config.h
/usr/include/php/ext/xml/expat_compat.h
/usr/include/php/ext/xml/php_xml.h
/usr/lib64/extensions/no-debug-non-zts-20100525/ctype.so
/usr/lib64/extensions/no-debug-non-zts-20100525/dom.so
/usr/lib64/extensions/no-debug-non-zts-20100525/fileinfo.so
/usr/lib64/extensions/no-debug-non-zts-20100525/filter.so
/usr/lib64/extensions/no-debug-non-zts-20100525/hash.so
/usr/lib64/extensions/no-debug-non-zts-20100525/iconv.so
/usr/lib64/extensions/no-debug-non-zts-20100525/json.so
/usr/lib64/extensions/no-debug-non-zts-20100525/pdo.so
/usr/lib64/extensions/no-debug-non-zts-20100525/phar.so
/usr/lib64/extensions/no-debug-non-zts-20100525/posix.so
/usr/lib64/extensions/no-debug-non-zts-20100525/session.so
/usr/lib64/extensions/no-debug-non-zts-20100525/simplexml.so
/usr/lib64/extensions/no-debug-non-zts-20100525/tokenizer.so
/usr/lib64/extensions/no-debug-non-zts-20100525/xml.so
/usr/lib64/extensions/no-debug-non-zts-20100525/xmlreader.so
/usr/lib64/extensions/no-debug-non-zts-20100525/xmlwriter.so

%changelog

