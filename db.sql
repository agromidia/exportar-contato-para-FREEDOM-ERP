--
-- Table structure for table `restAPI`
--

CREATE TABLE `restAPI` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(240) NOT NULL,
  `email` varchar(240) NOT NULL,
  `password` varchar(240) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
);
