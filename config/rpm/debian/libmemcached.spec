Name:		libmemcached
Version:	1.0.17
Release:	1%{?dist}
Summary:        libmemcached

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	libmemcached-1.0.17.tar.gz
# https://launchpad.net/libmemcached/1.0/1.0.17/+download/libmemcached-1.0.17.tar.gz
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
/usr/local/bin/memcapable
/usr/local/bin/memcat
/usr/local/bin/memcp
/usr/local/bin/memdump
/usr/local/bin/memerror
/usr/local/bin/memexist
/usr/local/bin/memflush
/usr/local/bin/memparse
/usr/local/bin/memping
/usr/local/bin/memrm
/usr/local/bin/memslap
/usr/local/bin/memstat
/usr/local/bin/memtouch
/usr/local/include/libhashkit-1.0/algorithm.h
/usr/local/include/libhashkit-1.0/behavior.h
/usr/local/include/libhashkit-1.0/configure.h
/usr/local/include/libhashkit-1.0/digest.h
/usr/local/include/libhashkit-1.0/function.h
/usr/local/include/libhashkit-1.0/has.h
/usr/local/include/libhashkit-1.0/hashkit.h
/usr/local/include/libhashkit-1.0/hashkit.hpp
/usr/local/include/libhashkit-1.0/str_algorithm.h
/usr/local/include/libhashkit-1.0/strerror.h
/usr/local/include/libhashkit-1.0/string.h
/usr/local/include/libhashkit-1.0/types.h
/usr/local/include/libhashkit-1.0/visibility.h
/usr/local/include/libhashkit/hashkit.h
/usr/local/include/libmemcached-1.0/alloc.h
/usr/local/include/libmemcached-1.0/allocators.h
/usr/local/include/libmemcached-1.0/analyze.h
/usr/local/include/libmemcached-1.0/auto.h
/usr/local/include/libmemcached-1.0/basic_string.h
/usr/local/include/libmemcached-1.0/behavior.h
/usr/local/include/libmemcached-1.0/callback.h
/usr/local/include/libmemcached-1.0/callbacks.h
/usr/local/include/libmemcached-1.0/configure.h
/usr/local/include/libmemcached-1.0/defaults.h
/usr/local/include/libmemcached-1.0/delete.h
/usr/local/include/libmemcached-1.0/deprecated_types.h
/usr/local/include/libmemcached-1.0/dump.h
/usr/local/include/libmemcached-1.0/encoding_key.h
/usr/local/include/libmemcached-1.0/error.h
/usr/local/include/libmemcached-1.0/exception.hpp
/usr/local/include/libmemcached-1.0/exist.h
/usr/local/include/libmemcached-1.0/fetch.h
/usr/local/include/libmemcached-1.0/flush.h
/usr/local/include/libmemcached-1.0/flush_buffers.h
/usr/local/include/libmemcached-1.0/get.h
/usr/local/include/libmemcached-1.0/hash.h
/usr/local/include/libmemcached-1.0/limits.h
/usr/local/include/libmemcached-1.0/memcached.h
/usr/local/include/libmemcached-1.0/memcached.hpp
/usr/local/include/libmemcached-1.0/options.h
/usr/local/include/libmemcached-1.0/parse.h
/usr/local/include/libmemcached-1.0/platform.h
/usr/local/include/libmemcached-1.0/quit.h
/usr/local/include/libmemcached-1.0/result.h
/usr/local/include/libmemcached-1.0/return.h
/usr/local/include/libmemcached-1.0/sasl.h
/usr/local/include/libmemcached-1.0/server.h
/usr/local/include/libmemcached-1.0/server_list.h
/usr/local/include/libmemcached-1.0/stats.h
/usr/local/include/libmemcached-1.0/storage.h
/usr/local/include/libmemcached-1.0/strerror.h
/usr/local/include/libmemcached-1.0/struct/allocator.h
/usr/local/include/libmemcached-1.0/struct/analysis.h
/usr/local/include/libmemcached-1.0/struct/callback.h
/usr/local/include/libmemcached-1.0/struct/memcached.h
/usr/local/include/libmemcached-1.0/struct/result.h
/usr/local/include/libmemcached-1.0/struct/sasl.h
/usr/local/include/libmemcached-1.0/struct/server.h
/usr/local/include/libmemcached-1.0/struct/stat.h
/usr/local/include/libmemcached-1.0/struct/string.h
/usr/local/include/libmemcached-1.0/touch.h
/usr/local/include/libmemcached-1.0/triggers.h
/usr/local/include/libmemcached-1.0/types.h
/usr/local/include/libmemcached-1.0/types/behavior.h
/usr/local/include/libmemcached-1.0/types/callback.h
/usr/local/include/libmemcached-1.0/types/connection.h
/usr/local/include/libmemcached-1.0/types/hash.h
/usr/local/include/libmemcached-1.0/types/return.h
/usr/local/include/libmemcached-1.0/types/server_distribution.h
/usr/local/include/libmemcached-1.0/verbosity.h
/usr/local/include/libmemcached-1.0/version.h
/usr/local/include/libmemcached-1.0/visibility.h
/usr/local/include/libmemcached/memcached.h
/usr/local/include/libmemcached/memcached.hpp
/usr/local/include/libmemcached/util.h
/usr/local/include/libmemcachedprotocol-0.0/binary.h
/usr/local/include/libmemcachedprotocol-0.0/callback.h
/usr/local/include/libmemcachedprotocol-0.0/handler.h
/usr/local/include/libmemcachedprotocol-0.0/vbucket.h
/usr/local/include/libmemcachedutil-1.0/flush.h
/usr/local/include/libmemcachedutil-1.0/ostream.hpp
/usr/local/include/libmemcachedutil-1.0/pid.h
/usr/local/include/libmemcachedutil-1.0/ping.h
/usr/local/include/libmemcachedutil-1.0/pool.h
/usr/local/include/libmemcachedutil-1.0/util.h
/usr/local/include/libmemcachedutil-1.0/version.h
/usr/local/lib/libhashkit.a
/usr/local/lib/libhashkit.la
/usr/local/lib/libhashkit.so
/usr/local/lib/libhashkit.so.2
/usr/local/lib/libhashkit.so.2.0.0
/usr/local/lib/libmemcached.a
/usr/local/lib/libmemcached.la
/usr/local/lib/libmemcached.so
/usr/local/lib/libmemcached.so.11
/usr/local/lib/libmemcached.so.11.0.0
/usr/local/lib/libmemcachedprotocol.a
/usr/local/lib/libmemcachedprotocol.la
/usr/local/lib/libmemcachedprotocol.so
/usr/local/lib/libmemcachedprotocol.so.0
/usr/local/lib/libmemcachedprotocol.so.0.0.0
/usr/local/lib/libmemcachedutil.a
/usr/local/lib/libmemcachedutil.la
/usr/local/lib/libmemcachedutil.so
/usr/local/lib/libmemcachedutil.so.2
/usr/local/lib/libmemcachedutil.so.2.0.0
/usr/local/lib/pkgconfig/libmemcached.pc
/usr/local/share/man/man1/memaslap.1
/usr/local/share/man/man1/memcapable.1
/usr/local/share/man/man1/memcat.1
/usr/local/share/man/man1/memcp.1
/usr/local/share/man/man1/memdump.1
/usr/local/share/man/man1/memerror.1
/usr/local/share/man/man1/memexist.1
/usr/local/share/man/man1/memflush.1
/usr/local/share/man/man1/memparse.1
/usr/local/share/man/man1/memping.1
/usr/local/share/man/man1/memrm.1
/usr/local/share/man/man1/memslap.1
/usr/local/share/man/man1/memstat.1
/usr/local/share/man/man1/memtouch.1
/usr/local/share/man/man3/hashkit_clone.3
/usr/local/share/man/man3/hashkit_crc32.3
/usr/local/share/man/man3/hashkit_create.3
/usr/local/share/man/man3/hashkit_fnv1_32.3
/usr/local/share/man/man3/hashkit_fnv1_64.3
/usr/local/share/man/man3/hashkit_fnv1a_32.3
/usr/local/share/man/man3/hashkit_fnv1a_64.3
/usr/local/share/man/man3/hashkit_free.3
/usr/local/share/man/man3/hashkit_functions.3
/usr/local/share/man/man3/hashkit_hsieh.3
/usr/local/share/man/man3/hashkit_is_allocated.3
/usr/local/share/man/man3/hashkit_jenkins.3
/usr/local/share/man/man3/hashkit_md5.3
/usr/local/share/man/man3/hashkit_murmur.3
/usr/local/share/man/man3/hashkit_value.3
/usr/local/share/man/man3/libhashkit.3
/usr/local/share/man/man3/libmemcached.3
/usr/local/share/man/man3/libmemcached_check_configuration.3
/usr/local/share/man/man3/libmemcached_configuration.3
/usr/local/share/man/man3/libmemcached_examples.3
/usr/local/share/man/man3/libmemcachedutil.3
/usr/local/share/man/man3/memcached.3
/usr/local/share/man/man3/memcached_add.3
/usr/local/share/man/man3/memcached_add_by_key.3
/usr/local/share/man/man3/memcached_analyze.3
/usr/local/share/man/man3/memcached_append.3
/usr/local/share/man/man3/memcached_append_by_key.3
/usr/local/share/man/man3/memcached_behavior_get.3
/usr/local/share/man/man3/memcached_behavior_set.3
/usr/local/share/man/man3/memcached_callback_get.3
/usr/local/share/man/man3/memcached_callback_set.3
/usr/local/share/man/man3/memcached_cas.3
/usr/local/share/man/man3/memcached_cas_by_key.3
/usr/local/share/man/man3/memcached_clone.3
/usr/local/share/man/man3/memcached_create.3
/usr/local/share/man/man3/memcached_decrement.3
/usr/local/share/man/man3/memcached_decrement_with_initial.3
/usr/local/share/man/man3/memcached_delete.3
/usr/local/share/man/man3/memcached_delete_by_key.3
/usr/local/share/man/man3/memcached_destroy_sasl_auth_data.3
/usr/local/share/man/man3/memcached_dump.3
/usr/local/share/man/man3/memcached_exist.3
/usr/local/share/man/man3/memcached_exist_by_key.3
/usr/local/share/man/man3/memcached_fetch.3
/usr/local/share/man/man3/memcached_fetch_execute.3
/usr/local/share/man/man3/memcached_fetch_result.3
/usr/local/share/man/man3/memcached_flush_buffers.3
/usr/local/share/man/man3/memcached_free.3
/usr/local/share/man/man3/memcached_generate_hash.3
/usr/local/share/man/man3/memcached_generate_hash_value.3
/usr/local/share/man/man3/memcached_get.3
/usr/local/share/man/man3/memcached_get_by_key.3
/usr/local/share/man/man3/memcached_get_memory_allocators.3
/usr/local/share/man/man3/memcached_get_sasl_callbacks.3
/usr/local/share/man/man3/memcached_get_user_data.3
/usr/local/share/man/man3/memcached_increment.3
/usr/local/share/man/man3/memcached_increment_with_initial.3
/usr/local/share/man/man3/memcached_last_error_message.3
/usr/local/share/man/man3/memcached_lib_version.3
/usr/local/share/man/man3/memcached_mget.3
/usr/local/share/man/man3/memcached_mget_by_key.3
/usr/local/share/man/man3/memcached_mget_execute.3
/usr/local/share/man/man3/memcached_mget_execute_by_key.3
/usr/local/share/man/man3/memcached_pool.3
/usr/local/share/man/man3/memcached_pool_behavior_get.3
/usr/local/share/man/man3/memcached_pool_behavior_set.3
/usr/local/share/man/man3/memcached_pool_create.3
/usr/local/share/man/man3/memcached_pool_destroy.3
/usr/local/share/man/man3/memcached_pool_fetch.3
/usr/local/share/man/man3/memcached_pool_pop.3
/usr/local/share/man/man3/memcached_pool_push.3
/usr/local/share/man/man3/memcached_pool_release.3
/usr/local/share/man/man3/memcached_pool_st.3
/usr/local/share/man/man3/memcached_prepend.3
/usr/local/share/man/man3/memcached_prepend_by_key.3
/usr/local/share/man/man3/memcached_quit.3
/usr/local/share/man/man3/memcached_replace.3
/usr/local/share/man/man3/memcached_replace_by_key.3
/usr/local/share/man/man3/memcached_sasl_set_auth_data.3
/usr/local/share/man/man3/memcached_server_add.3
/usr/local/share/man/man3/memcached_server_count.3
/usr/local/share/man/man3/memcached_server_cursor.3
/usr/local/share/man/man3/memcached_server_list.3
/usr/local/share/man/man3/memcached_server_list_append.3
/usr/local/share/man/man3/memcached_server_list_count.3
/usr/local/share/man/man3/memcached_server_list_free.3
/usr/local/share/man/man3/memcached_server_push.3
/usr/local/share/man/man3/memcached_servers_parse.3
/usr/local/share/man/man3/memcached_set.3
/usr/local/share/man/man3/memcached_set_by_key.3
/usr/local/share/man/man3/memcached_set_memory_allocators.3
/usr/local/share/man/man3/memcached_set_sasl_callbacks.3
/usr/local/share/man/man3/memcached_set_user_data.3
/usr/local/share/man/man3/memcached_stat.3
/usr/local/share/man/man3/memcached_stat_execute.3
/usr/local/share/man/man3/memcached_stat_get_keys.3
/usr/local/share/man/man3/memcached_stat_get_value.3
/usr/local/share/man/man3/memcached_stat_servername.3
/usr/local/share/man/man3/memcached_strerror.3
/usr/local/share/man/man3/memcached_touch.3
/usr/local/share/man/man3/memcached_touch_by_key.3
/usr/local/share/man/man3/memcached_verbosity.3
/usr/local/share/man/man3/memcached_version.3

%changelog

