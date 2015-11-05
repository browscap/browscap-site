ALTER TABLE `downloadLog`
  ADD INDEX `idx_ip_date` (`ipAddress`, `downloadDate`) USING BTREE;
