;-----------------------------------------------------------------------------------
; NOTICE:
; Any entries set in this file will override the voicemail GUI and force the GUI
; to show the value of these entries. It will prevent the GUI from making changes
; and saving them out. If you want to manage these or any other settings from the
; GUI you should remove or comment them out here.
;-----------------------------------------------------------------------------------


; 1st listed format gets emailed
;format=wav49|wav

;attach=yes
;pbxskip=yes              	; Don't put [PBX]: in the subject line
;serveremail=vm@asterisk		; Who the e-mail notification should appear to come from
;fromstring=Voicemail System 	; Real name of email sender
;maxsilence=5            	; Wait for 5 silent seconds and end the voicemail
;silencethreshold=128     	; What do we consider to be silence
;skipms=3000			; How many miliseconds to skip forward/back when rew/ff in message playback
;review=yes			; Allow sender to review/rerecord their message before saving it
;operator=yes			; Allow caller to press 0
;nextaftercmd=yes		; Automagically play next message when current message is deleted or saved

; Asterisk pre 1.6.2 options:
;maxmessage=180          	; max length of vm message
;minmessage=3			; Minimum length of a voicemail message in seconds

; Asterisk post 1.6.2 options the minmessage and maxmessage are deprecated in favor of these
; which are per user settable also:
;maxsecs=180          		; max length of vm message
;minsecs=3			; Minimum length of a voicemail message in seconds

; If using Asterisk 1.6 and ARI or other sources for mail, you should set pollmailboxes to
; yes and pollfreq if you want to change the default 30 sec polling
; 
;pollmailboxes=yes
;pollfreq=30
; If using IMAP storage, the username and password are specified in the user/extension  
; configuration, while the global settings are here 
;  
;imapserver=server.domain.com   ; Specify the IP address or hostname of the IMAP server 
;imapflags=notls      		; Specify any IMAP flags needed here ("notls" for example) 
;imapfolder=Inbox      		; Specify the IMAP folder where voicemails and/or greetings are stored 
;expungeonhangup=yes      	; Mark messages for deletion on hangup 
;imapgreetings=yes      	; Store greetings in IMAP as well 
;
;forcename=yes         		; Forces a new user to record their name.  A new user is
                    		; determined by the password being the same as
                    		; the mailbox number.  The default is "no".
;forcegreetings=yes    		; This is the same as forcename, except for recording
                    		; greetings.  The default is "no".
