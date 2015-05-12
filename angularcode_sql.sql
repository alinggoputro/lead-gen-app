


-- Table structure for table `agents`

CREATE TABLE `agents` (
`id` int(11) NOT NULL AUTO_INCREMENT, `first_name` varchar(255) DEFAULT NULL, `last_name` varchar(255) DEFAULT NULL, `email` varchar(255) DEFAULT NULL, `mobile` varchar(255) DEFAULT NULL, `created` datetime DEFAULT NULL, `modified` datetime DEFAULT NULL, `active` int(1) NOT NULL DEFAULT 1, PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

-- Table structure for table `leads`


CREATE TABLE `leads` (
`id` int(11) NOT NULL AUTO_INCREMENT, `agent_id` int(11) DEFAULT NULL, `first_name` int(11) DEFAULT NULL, `last_name` int(11) DEFAULT NULL, `email` varchar(255) DEFAULT NULL, `mobile` varchar(45) DEFAULT NULL, `message` text,
`created` datetime DEFAULT NULL, `modified` datetime DEFAULT NULL, PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `leads` ADD CONSTRAINT `fk_leads_agents`
FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- Dumping data for table `agents`

INSERT INTO `agents` (`id`, `first_name`, `last_name`, `email`, `mobile`, `created`, `modified`,`active`) VALUES
(1, 'Chris', 'Conduct', 'chris@conducthq.com', '9726893743', '2013-10-24 16:44:23', '2013-10-24 16:44:23',0),
(2, 'Charlie', 'Conduct', 'charlie@conducthq.com', '8383739948', '2013-10-24 16:47:00', '2013-10-24 16:47:00',1),
(3, 'Simon', 'Conduct', 'simon@conducthq.com', '8284934859', '2013-10-24 16:58:46', '2013-10-24 16:58:46',0),
(4, 'Steve', 'Conduct', 'steve@conducthq.com', '8284934859', '2013-10-24 17:01:38', '2013-10-24 17:01:38',1);

