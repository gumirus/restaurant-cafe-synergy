using System;
using MySql.Data.MySqlClient;

namespace RestaurantApp;

public static class DatabaseHelper
{
    // Измени строку подключения под свой XAMPP
    private static readonly string ConnectionString = "Server=localhost;Database=csharp_users_db;User Id=root;Password=;";

    public static MySqlConnection GetConnection()
    {
        return new MySqlConnection(ConnectionString);
    }

    public static void InitializeDatabase()
    {
        try
        {
            // Сначала создаём БД, если нет
            using var conn = new MySqlConnection("Server=localhost;User Id=root;Password=;");
            conn.Open();
            var cmd = new MySqlCommand("CREATE DATABASE IF NOT EXISTS csharp_users_db CHARACTER SET utf8 COLLATE utf8_general_ci;", conn);
            cmd.ExecuteNonQuery();
            conn.Close();

            // Создаём таблицу users
            using var conn2 = GetConnection();
            conn2.Open();
            var createTable = @"
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    firstName VARCHAR(100) NOT NULL,
                    lastName VARCHAR(100) NOT NULL,
                    username VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            var cmd2 = new MySqlCommand(createTable, conn2);
            cmd2.ExecuteNonQuery();
            conn2.Close();

            // Создаём таблицу menu_items (для меню ресторана)
            using var conn3 = GetConnection();
            conn3.Open();
            var createMenu = @"
                CREATE TABLE IF NOT EXISTS menu_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(200) NOT NULL,
                    category VARCHAR(100) NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            var cmd3 = new MySqlCommand(createMenu, conn3);
            cmd3.ExecuteNonQuery();
            conn3.Close();

            // Создаём таблицу reservations (бронирования)
            using var conn4 = GetConnection();
            conn4.Open();
            var createReservations = @"
                CREATE TABLE IF NOT EXISTS reservations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    guest_name VARCHAR(200) NOT NULL,
                    phone VARCHAR(20) NOT NULL,
                    guests_count INT NOT NULL,
                    reservation_date DATE NOT NULL,
                    reservation_time TIME NOT NULL,
                    status VARCHAR(50) DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )";
            var cmd4 = new MySqlCommand(createReservations, conn4);
            cmd4.ExecuteNonQuery();
            conn4.Close();

            // Добавляем тестовые данные в меню
            using var conn5 = GetConnection();
            conn5.Open();
            var checkData = new MySqlCommand("SELECT COUNT(*) FROM menu_items", conn5);
            long count = (long)checkData.ExecuteScalar();
            if (count == 0)
            {
                var insertData = @"
                    INSERT INTO menu_items (name, category, price, description) VALUES
                    ('Цезарь с курицей', 'Салаты', 450.00, 'Классический салат с курицей, пармезаном и соусом'),
                    ('Греческий салат', 'Салаты', 380.00, 'Свежие овощи с сыром фета'),
                    ('Борщ', 'Супы', 320.00, 'Традиционный борщ со сметаной'),
                    ('Крем-суп из грибов', 'Супы', 350.00, 'Нежный грибной крем-суп'),
                    ('Стейк Рибай', 'Горячее', 1200.00, 'Сочный стейк из мраморной говядины'),
                    ('Лосось на гриле', 'Горячее', 980.00, 'Филе лосося с овощами гриль'),
                    ('Тирамису', 'Десерты', 350.00, 'Классический итальянский десерт'),
                    ('Чизкейк', 'Десерты', 320.00, 'Нью-Йорк чизкейк с ягодным соусом'),
                    ('Капучино', 'Напитки', 180.00, 'Кофе с молочной пенкой'),
                    ('Чай зеленый', 'Напитки', 120.00, 'Зеленый чай с жасмином');";
                var cmd5 = new MySqlCommand(insertData, conn5);
                cmd5.ExecuteNonQuery();
            }
            conn5.Close();
        }
        catch (Exception ex)
        {
            System.Windows.Forms.MessageBox.Show($"Ошибка инициализации БД: {ex.Message}", "Ошибка",
                System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Error);
        }
    }

    public static Dictionary<string, string>? AuthenticateUser(string username, string password)
    {
        try
        {
            using var conn = GetConnection();
            conn.Open();
            string sql = "SELECT * FROM users WHERE username = @username AND password = @password";
            using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@username", username);
            cmd.Parameters.AddWithValue("@password", password);
            using var reader = cmd.ExecuteReader();
            if (reader.Read())
            {
                return new Dictionary<string, string>
                {
                    { "id", reader["id"].ToString() ?? "" },
                    { "firstName", reader["firstName"].ToString() ?? "" },
                    { "lastName", reader["lastName"].ToString() ?? "" },
                    { "username", reader["username"].ToString() ?? "" },
                    { "name", $"{reader["firstName"]} {reader["lastName"]}" }
                };
            }
        }
        catch (Exception ex)
        {
            System.Windows.Forms.MessageBox.Show($"Ошибка подключения к БД: {ex.Message}", "Ошибка",
                System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Error);
        }
        return null;
    }

    public static string RegisterUser(string firstName, string lastName, string username, string password)
    {
        try
        {
            using var conn = GetConnection();
            conn.Open();

            // Проверка на дубликат логина
            var checkCmd = new MySqlCommand("SELECT COUNT(*) FROM users WHERE username = @username", conn);
            checkCmd.Parameters.AddWithValue("@username", username);
            long exists = (long)checkCmd.ExecuteScalar();
            if (exists > 0)
                return "Пользователь с таким логином уже существует!";

            string sql = "INSERT INTO users (firstName, lastName, username, password) VALUES (@fn, @ln, @u, @p)";
            using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@fn", firstName);
            cmd.Parameters.AddWithValue("@ln", lastName);
            cmd.Parameters.AddWithValue("@u", username);
            cmd.Parameters.AddWithValue("@p", password);
            cmd.ExecuteNonQuery();
            return "ok";
        }
        catch (Exception ex)
        {
            return $"Ошибка регистрации: {ex.Message}";
        }
    }

    public static List<Dictionary<string, object>> GetMenuItems(string? category = null)
    {
        var items = new List<Dictionary<string, object>>();
        try
        {
            using var conn = GetConnection();
            conn.Open();
            string sql = "SELECT * FROM menu_items";
            if (!string.IsNullOrEmpty(category))
                sql += " WHERE category = @category";
            sql += " ORDER BY category, name";

            using var cmd = new MySqlCommand(sql, conn);
            if (!string.IsNullOrEmpty(category))
                cmd.Parameters.AddWithValue("@category", category);

            using var reader = cmd.ExecuteReader();
            while (reader.Read())
            {
                var item = new Dictionary<string, object>
                {
                    { "id", reader["id"] },
                    { "name", reader["name"].ToString() ?? "" },
                    { "category", reader["category"].ToString() ?? "" },
                    { "price", Convert.ToDouble(reader["price"]) },
                    { "description", reader["description"].ToString() ?? "" }
                };
                items.Add(item);
            }
        }
        catch (Exception ex)
        {
            System.Windows.Forms.MessageBox.Show($"Ошибка загрузки меню: {ex.Message}", "Ошибка",
                System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Error);
        }
        return items;
    }

    public static List<Dictionary<string, object>> GetReservations(int userId)
    {
        var list = new List<Dictionary<string, object>>();
        try
        {
            using var conn = GetConnection();
            conn.Open();
            string sql = "SELECT * FROM reservations WHERE user_id = @uid ORDER BY reservation_date DESC";
            using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@uid", userId);
            using var reader = cmd.ExecuteReader();
            while (reader.Read())
            {
                var item = new Dictionary<string, object>
                {
                    { "id", reader["id"] },
                    { "guest_name", reader["guest_name"].ToString() ?? "" },
                    { "phone", reader["phone"].ToString() ?? "" },
                    { "guests_count", reader["guests_count"] },
                    { "reservation_date", reader["reservation_date"].ToString() ?? "" },
                    { "reservation_time", reader["reservation_time"].ToString() ?? "" },
                    { "status", reader["status"].ToString() ?? "" }
                };
                list.Add(item);
            }
        }
        catch { }
        return list;
    }

    public static bool AddReservation(int userId, string guestName, string phone, int guests, string date, string time)
    {
        try
        {
            using var conn = GetConnection();
            conn.Open();
            string sql = "INSERT INTO reservations (user_id, guest_name, phone, guests_count, reservation_date, reservation_time) VALUES (@uid, @gn, @ph, @gc, @rd, @rt)";
            using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@uid", userId);
            cmd.Parameters.AddWithValue("@gn", guestName);
            cmd.Parameters.AddWithValue("@ph", phone);
            cmd.Parameters.AddWithValue("@gc", guests);
            cmd.Parameters.AddWithValue("@rd", date);
            cmd.Parameters.AddWithValue("@rt", time);
            cmd.ExecuteNonQuery();
            return true;
        }
        catch (Exception ex)
        {
            System.Windows.Forms.MessageBox.Show($"Ошибка бронирования: {ex.Message}", "Ошибка",
                System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Error);
            return false;
        }
    }

    public static bool DeleteReservation(int reservationId)
    {
        try
        {
            using var conn = GetConnection();
            conn.Open();
            string sql = "DELETE FROM reservations WHERE id = @id";
            using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@id", reservationId);
            cmd.ExecuteNonQuery();
            return true;
        }
        catch { return false; }
    }
}
