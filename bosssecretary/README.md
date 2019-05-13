### bosssecretary module for IssabelPBX

The boss-secretary module creates a special **ring group** which includes
one or more "bosses" and one or more secretaries". When someone calls
the boss' extension, the secretary (or secretaries) extension will ring only, 
allowing the secretary to answer his or her boss' call.

Additionally, you can define one or more chief extensions, 
who may call boss extension directly without ringing the secretary's extension.

The module includes codes for activating, deactivating and toggling the
groups' state. For example, when a secretary ends her working day, she
may turn off the boss-secretary group dialing *152<ext number>, so her
boss will receive calls directly. 

The module generates the appropriate hints to have ip phones show the
groups state by subscribing to the *152<ext number> extension. 
You can set this functionality to blf button on your phone.

For example for Linksys/ Cisco phones:

`fnc=blf+sd;sub=*152EXT@$PROXY;ext=*152@PROXY`
