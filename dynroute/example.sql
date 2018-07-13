;    dynroute - Dynamic Route Module for IssabelPBX
;    Copyright (C) 2009-2014 John Fawcett john@voipsupport.it
;
;    This program is free software: you can redistribute it and/or modify
;    it under the terms of the GNU General Public License as published by
;    the Free Software Foundation, either version 3 of the License, or
;    any later version.
;
;    This program is distributed in the hope that it will be useful,
;    but WITHOUT ANY WARRANTY; without even the implied warranty of
;    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
;    GNU General Public License for more details.
;
;    You should have received a copy of the GNU General Public License
;    along with this program.  If not, see <http://www.gnu.org/licenses/>.


; Examples to create database and table for 
; testing dynamic routing


CREATE DATABASE `customer_routing`;

USE `customer_routing`;


CREATE TABLE `customer_routing` (
  `customer_routing_id` int(11) NOT NULL AUTO_INCREMENT,
  `callerid` varchar(25) NOT NULL,
  `result` varchar(25) NOT NULL,
  PRIMARY KEY (`customer_routing_id`),
  KEY `callerid` (`callerid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



