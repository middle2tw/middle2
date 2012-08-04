package Perlbal::Plugin::Hisoku;

use strict;
use warnings;
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

            my $vhost = $hds->header("Host");

            my $ipport = `sudo -u ec2-user /srv/code/hisoku/scripts/get-nodes-by-domain $vhost`;
            $ipport =~ s/\s+$//m;
            my ($ip, $port) = split(':', $ipport);

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
