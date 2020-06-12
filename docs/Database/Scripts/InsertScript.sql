
INSERT INTO `userRole`(`id`, `name`) VALUES (1,'Admin');
INSERT INTO `userRole`(`id`, `name`) VALUES (2,'Technik');
INSERT INTO `userRole`(`id`, `name`) VALUES (3,'Klient');

INSERT INTO `officeState` (`id`, `name`) VALUES
(4, 'Collected'),
(3, 'Ready for Pickup'),
(2, 'In use'),
(1, 'starting');

INSERT INTO `user` (`id`, `firstName`, `lastName`, `email`, `password`, `tempPassword` , `fk_ClientSupervisor_Id`, `fk_UserRole_Id`, `fk_Technician_Id`) VALUES
(1, 'Admin', 'Admin', 'Adm@adm.cz', '$2y$10$i5at1myh753dOURT6r2R9OQY9VXnuQ7Xu1wOu3PYC5sZXAsOeFYki', 0 , NULL, 1, NULL);