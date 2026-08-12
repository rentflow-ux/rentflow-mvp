CREATE TABLE IF NOT EXISTS vehicles(id INT AUTO_INCREMENT PRIMARY KEY,make VARCHAR(80),model VARCHAR(80),category VARCHAR(40),transmission VARCHAR(20),daily_rate DECIMAL(10,2),active TINYINT DEFAULT 1);
CREATE TABLE IF NOT EXISTS bookings(id INT AUTO_INCREMENT PRIMARY KEY,customer_name VARCHAR(120),phone VARCHAR(40),vehicle_id INT,pickup_date DATE,return_date DATE,pickup_location VARCHAR(120),total DECIMAL(10,2),status VARCHAR(30),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(vehicle_id) REFERENCES vehicles(id));
INSERT INTO vehicles(make,model,category,transmission,daily_rate) VALUES ('Dacia','Sandero','economy','manual',300),('Renault','Clio','automatic','automatic',380),('Hyundai','Tucson','suv','automatic',650),('Mercedes','C-Class','premium','automatic',1100);

