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
%configure
cd contrib/fb303
./bootstrap.sh
%configure CPPFLAGS="-DHAVE_INTTYPES_H -DHAVE_NETINET_IN_H" --with-thriftpath=/usr/
make

%install
rm -rf %{buildroot}
make install DESTDIR=%{buildroot}
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
/usr/lib64/libfb303.a
/usr/share/fb303/if/fb303.thrift
/usr/bin/thrift
/usr/include/thrift/TApplicationException.h
/usr/include/thrift/TDispatchProcessor.h
/usr/include/thrift/TLogging.h
/usr/include/thrift/TProcessor.h
/usr/include/thrift/TReflectionLocal.h
/usr/include/thrift/Thrift.h
/usr/include/thrift/async/TAsyncBufferProcessor.h
/usr/include/thrift/async/TAsyncChannel.h
/usr/include/thrift/async/TAsyncDispatchProcessor.h
/usr/include/thrift/async/TAsyncProcessor.h
/usr/include/thrift/async/TAsyncProtocolProcessor.h
/usr/include/thrift/async/TEvhttpClientChannel.h
/usr/include/thrift/async/TEvhttpServer.h
/usr/include/thrift/concurrency/BoostThreadFactory.h
/usr/include/thrift/concurrency/Exception.h
/usr/include/thrift/concurrency/FunctionRunner.h
/usr/include/thrift/concurrency/Monitor.h
/usr/include/thrift/concurrency/Mutex.h
/usr/include/thrift/concurrency/PlatformThreadFactory.h
/usr/include/thrift/concurrency/PosixThreadFactory.h
/usr/include/thrift/concurrency/Thread.h
/usr/include/thrift/concurrency/ThreadManager.h
/usr/include/thrift/concurrency/TimerManager.h
/usr/include/thrift/concurrency/Util.h
/usr/include/thrift/config.h
/usr/include/thrift/processor/PeekProcessor.h
/usr/include/thrift/processor/StatsProcessor.h
/usr/include/thrift/protocol/TBase64Utils.h
/usr/include/thrift/protocol/TBinaryProtocol.h
/usr/include/thrift/protocol/TBinaryProtocol.tcc
/usr/include/thrift/protocol/TCompactProtocol.h
/usr/include/thrift/protocol/TCompactProtocol.tcc
/usr/include/thrift/protocol/TDebugProtocol.h
/usr/include/thrift/protocol/TDenseProtocol.h
/usr/include/thrift/protocol/TJSONProtocol.h
/usr/include/thrift/protocol/TProtocol.h
/usr/include/thrift/protocol/TProtocolException.h
/usr/include/thrift/protocol/TProtocolTap.h
/usr/include/thrift/protocol/TVirtualProtocol.h
/usr/include/thrift/qt/TQIODeviceTransport.h
/usr/include/thrift/qt/TQTcpServer.h
/usr/include/thrift/server/TNonblockingServer.h
/usr/include/thrift/server/TServer.h
/usr/include/thrift/server/TSimpleServer.h
/usr/include/thrift/server/TThreadPoolServer.h
/usr/include/thrift/server/TThreadedServer.h
/usr/include/thrift/transport/TBufferTransports.h
/usr/include/thrift/transport/TFDTransport.h
/usr/include/thrift/transport/TFileTransport.h
/usr/include/thrift/transport/THttpClient.h
/usr/include/thrift/transport/THttpServer.h
/usr/include/thrift/transport/THttpTransport.h
/usr/include/thrift/transport/TPipe.h
/usr/include/thrift/transport/TPipeServer.h
/usr/include/thrift/transport/TSSLServerSocket.h
/usr/include/thrift/transport/TSSLSocket.h
/usr/include/thrift/transport/TServerSocket.h
/usr/include/thrift/transport/TServerTransport.h
/usr/include/thrift/transport/TShortReadTransport.h
/usr/include/thrift/transport/TSimpleFileTransport.h
/usr/include/thrift/transport/TSocket.h
/usr/include/thrift/transport/TSocketPool.h
/usr/include/thrift/transport/TTransport.h
/usr/include/thrift/transport/TTransportException.h
/usr/include/thrift/transport/TTransportUtils.h
/usr/include/thrift/transport/TVirtualTransport.h
/usr/include/thrift/transport/TZlibTransport.h
/usr/lib/python2.6/site-packages/thrift-0.9.0-py2.6.egg-info/PKG-INFO
/usr/lib/python2.6/site-packages/thrift-0.9.0-py2.6.egg-info/SOURCES.txt
/usr/lib/python2.6/site-packages/thrift-0.9.0-py2.6.egg-info/dependency_links.txt
/usr/lib/python2.6/site-packages/thrift-0.9.0-py2.6.egg-info/top_level.txt
/usr/lib/python2.6/site-packages/thrift/TSCons.py
/usr/lib/python2.6/site-packages/thrift/TSCons.pyc
/usr/lib/python2.6/site-packages/thrift/TSCons.pyo
/usr/lib/python2.6/site-packages/thrift/TSerialization.py
/usr/lib/python2.6/site-packages/thrift/TSerialization.pyc
/usr/lib/python2.6/site-packages/thrift/TSerialization.pyo
/usr/lib/python2.6/site-packages/thrift/Thrift.py
/usr/lib/python2.6/site-packages/thrift/Thrift.pyc
/usr/lib/python2.6/site-packages/thrift/Thrift.pyo
/usr/lib/python2.6/site-packages/thrift/__init__.py
/usr/lib/python2.6/site-packages/thrift/__init__.pyc
/usr/lib/python2.6/site-packages/thrift/__init__.pyo
/usr/lib/python2.6/site-packages/thrift/protocol/TBase.py
/usr/lib/python2.6/site-packages/thrift/protocol/TBase.pyc
/usr/lib/python2.6/site-packages/thrift/protocol/TBase.pyo
/usr/lib/python2.6/site-packages/thrift/protocol/TBinaryProtocol.py
/usr/lib/python2.6/site-packages/thrift/protocol/TBinaryProtocol.pyc
/usr/lib/python2.6/site-packages/thrift/protocol/TBinaryProtocol.pyo
/usr/lib/python2.6/site-packages/thrift/protocol/TCompactProtocol.py
/usr/lib/python2.6/site-packages/thrift/protocol/TCompactProtocol.pyc
/usr/lib/python2.6/site-packages/thrift/protocol/TCompactProtocol.pyo
/usr/lib/python2.6/site-packages/thrift/protocol/TProtocol.py
/usr/lib/python2.6/site-packages/thrift/protocol/TProtocol.pyc
/usr/lib/python2.6/site-packages/thrift/protocol/TProtocol.pyo
/usr/lib/python2.6/site-packages/thrift/protocol/__init__.py
/usr/lib/python2.6/site-packages/thrift/protocol/__init__.pyc
/usr/lib/python2.6/site-packages/thrift/protocol/__init__.pyo
/usr/lib/python2.6/site-packages/thrift/server/THttpServer.py
/usr/lib/python2.6/site-packages/thrift/server/THttpServer.pyc
/usr/lib/python2.6/site-packages/thrift/server/THttpServer.pyo
/usr/lib/python2.6/site-packages/thrift/server/TNonblockingServer.py
/usr/lib/python2.6/site-packages/thrift/server/TNonblockingServer.pyc
/usr/lib/python2.6/site-packages/thrift/server/TNonblockingServer.pyo
/usr/lib/python2.6/site-packages/thrift/server/TProcessPoolServer.py
/usr/lib/python2.6/site-packages/thrift/server/TProcessPoolServer.pyc
/usr/lib/python2.6/site-packages/thrift/server/TProcessPoolServer.pyo
/usr/lib/python2.6/site-packages/thrift/server/TServer.py
/usr/lib/python2.6/site-packages/thrift/server/TServer.pyc
/usr/lib/python2.6/site-packages/thrift/server/TServer.pyo
/usr/lib/python2.6/site-packages/thrift/server/__init__.py
/usr/lib/python2.6/site-packages/thrift/server/__init__.pyc
/usr/lib/python2.6/site-packages/thrift/server/__init__.pyo
/usr/lib/python2.6/site-packages/thrift/transport/THttpClient.py
/usr/lib/python2.6/site-packages/thrift/transport/THttpClient.pyc
/usr/lib/python2.6/site-packages/thrift/transport/THttpClient.pyo
/usr/lib/python2.6/site-packages/thrift/transport/TSSLSocket.py
/usr/lib/python2.6/site-packages/thrift/transport/TSSLSocket.pyc
/usr/lib/python2.6/site-packages/thrift/transport/TSSLSocket.pyo
/usr/lib/python2.6/site-packages/thrift/transport/TSocket.py
/usr/lib/python2.6/site-packages/thrift/transport/TSocket.pyc
/usr/lib/python2.6/site-packages/thrift/transport/TSocket.pyo
/usr/lib/python2.6/site-packages/thrift/transport/TTransport.py
/usr/lib/python2.6/site-packages/thrift/transport/TTransport.pyc
/usr/lib/python2.6/site-packages/thrift/transport/TTransport.pyo
/usr/lib/python2.6/site-packages/thrift/transport/TTwisted.py
/usr/lib/python2.6/site-packages/thrift/transport/TTwisted.pyc
/usr/lib/python2.6/site-packages/thrift/transport/TTwisted.pyo
/usr/lib/python2.6/site-packages/thrift/transport/TZlibTransport.py
/usr/lib/python2.6/site-packages/thrift/transport/TZlibTransport.pyc
/usr/lib/python2.6/site-packages/thrift/transport/TZlibTransport.pyo
/usr/lib/python2.6/site-packages/thrift/transport/__init__.py
/usr/lib/python2.6/site-packages/thrift/transport/__init__.pyc
/usr/lib/python2.6/site-packages/thrift/transport/__init__.pyo
/usr/lib64/libthrift-0.9.0.so
/usr/lib64/libthrift.a
/usr/lib64/libthrift.la
/usr/lib64/libthrift.so
/usr/lib64/libthriftz-0.9.0.so
/usr/lib64/libthriftz.a
/usr/lib64/libthriftz.la
/usr/lib64/libthriftz.so
/usr/lib64/pkgconfig/thrift-z.pc
/usr/lib64/pkgconfig/thrift.pc

%changelog

