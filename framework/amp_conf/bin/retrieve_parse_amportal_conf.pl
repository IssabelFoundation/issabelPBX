#!/usr/bin/perl -w
#
# Julien BLACHE <julien.blache@linbox.com>
#
# Released under the terms of the GNU General Public License *v2* as published by
# the Free Software Foundation.
# amportal config parser for retrieve_*.pl

sub parse_amportal_conf
{
	my $filename = $_[0];
	my %ampconf = (
		AMPDBENGINE => "mysql",
		AMPDBNAME => "asterisk",
		AMPENGINE => "asterisk",
	);
	
	open(AMPCONF, $filename) or die "Cannot open $filename ($!)";
	
	while (<AMPCONF>)
	{
		if ($_ =~ /^\s*([a-zA-Z0-9_]+)\s*=\s*(.*)\s*([;#].*)?/)
		{
			$ampconf{$1} = $2;
		}
	}
	close(AMPCONF);
	
	return \%ampconf;
}

# perl depends on this
1;
