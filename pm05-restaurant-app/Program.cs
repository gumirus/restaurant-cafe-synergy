namespace RestaurantApp;

static class Program
{
    [STAThread]
    static void Main()
    {
        ApplicationConfiguration.Initialize();

        // Инициализация БД (создание таблиц, если нет)
        DatabaseHelper.InitializeDatabase();

        Application.Run(new Forms.LoginForm());
    }
}
