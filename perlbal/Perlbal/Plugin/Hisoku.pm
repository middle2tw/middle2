package Perlbal::Plugin::Hisoku;

use strict;
use warnings;
use WWW::Curl::Easy;
use JSON -support_by_pp;
no  warnings qw(deprecated);

# when "LOAD" directive loads us up
sub load {
    my $class = shift;
    return 1;
}

# unload our global commands, clear our service object
sub unload {
    my $class = shift;
    return 1;
}

# called when we're being added to a service
sub register {
    my ($class, $svc) = @_;

    $svc->register_hook('Hisoku', 'start_http_request', sub {
            my Perlbal::ClientHTTPBase $client = shift;
            my Perlbal::HTTPHeaders $hds = $client->{req_headers};
            return 0 unless $hds;

            my ($vhost_ip, $vhost_port) = split(':', $hds->header("Host"));
            $vhost_port = 80 unless defined $vhost_port;

            my $curl = WWW::Curl::Easy->new;
            $curl->setopt(CURLOPT_HEADER,1);
            $curl->setopt(CURLOPT_URL, 'http://hisoku.ronny.tw/api/getnodes?domain=' . $vhost_ip . '&port=' . $vhost_port);

            my $response_body;
            open (my $fileb, ">", \$response_body);
            $curl->setopt(CURLOPT_WRITEDATA, $fileb);
            my $retcode = $curl->perform;
            my $json;
            $json = from_json($response_body, {utf8 => 1});
            my $ip = $json->{'nodes'}[0][0];
            my $port = $json->{'nodes'}[0][1];

            if (my $be = Perlbal::BackendHTTP->new($svc, $ip, $port, { pool => $svc->{pool} })) {
                $svc->add_pending_connect($be);
            }
    });

    return 1;
}

# called when we're no longer active on a service
sub unregister {
    my ($class, $svc) = @_;

    $svc->unregister_hook('Hisoku');
    return 1;
}

sub dumpconfig {
    my ($class, $svc) = @_;
    return 1;
}

1;
