using System.Drawing;
using System.Windows.Forms;

namespace RestaurantApp.Forms;

public class MainForm : Form
{
    private Dictionary<string, string> _user;
    private TabControl tabControl;
    private TabPage tabMenu;
    private TabPage tabReservations;
    private TabPage tabProfile;
    private DataGridView dataGridViewMenu;
    private DataGridView dataGridViewReservations;
    private Panel panelHeader;

    public MainForm(Dictionary<string, string> user)
    {
        _user = user;
        InitializeComponent();
        LoadMenu();
        LoadReservations();
    }

    private void InitializeComponent()
    {
        this.Size = new Size(900, 650);
        this.StartPosition = FormStartPosition.CenterScreen;
        this.FormBorderStyle = FormBorderStyle.None;
        this.BackColor = Color.FromArgb(245, 245, 245);

        // Верхняя панель
        panelHeader = new Panel
        {
            Dock = DockStyle.Top,
            Height = 60,
            BackColor = Color.FromArgb(255, 69, 0)
        };

        var labelWelcome = new Label
        {
            Text = $"FlavorHouse Bistro — {_user["name"]}",
            Font = new Font("Segoe UI", 16, FontStyle.Bold),
            ForeColor = Color.White,
            Location = new Point(20, 15),
            Size = new Size(500, 35)
        };

        var labelLogout = new Label
        {
            Text = "Выйти",
            Font = new Font("Segoe UI", 10, FontStyle.Underline),
            ForeColor = Color.White,
            Location = new Point(800, 20),
            Size = new Size(60, 25),
            TextAlign = ContentAlignment.MiddleCenter,
            Cursor = Cursors.Hand
        };
        labelLogout.Click += (s, e) =>
        {
            var loginForm = new LoginForm();
            loginForm.Show();
            this.Close();
        };

        var labelClose = new Label
        {
            Text = "X",
            Font = new Font("Segoe UI", 14, FontStyle.Bold),
            ForeColor = Color.White,
            Location = new Point(860, 15),
            Size = new Size(25, 25),
            TextAlign = ContentAlignment.MiddleCenter,
            Cursor = Cursors.Hand
        };
        labelClose.MouseEnter += (s, e) => labelClose.ForeColor = Color.DarkOrange;
        labelClose.MouseLeave += (s, e) => labelClose.ForeColor = Color.White;
        labelClose.Click += (s, e) => Application.Exit();

        panelHeader.Controls.Add(labelWelcome);
        panelHeader.Controls.Add(labelLogout);
        panelHeader.Controls.Add(labelClose);

        // TabControl
        tabControl = new TabControl
        {
            Dock = DockStyle.Fill,
            Font = new Font("Segoe UI", 11),
            Padding = new Point(10, 5)
        };

        // Вкладка Меню
        tabMenu = new TabPage("Меню ресторана");
        dataGridViewMenu = new DataGridView
        {
            Dock = DockStyle.Fill,
            AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill,
            ReadOnly = true,
            AllowUserToAddRows = false,
            RowHeadersVisible = false,
            BackgroundColor = Color.White,
            Font = new Font("Segoe UI", 10),
            BorderStyle = BorderStyle.None,
            AlternatingRowsDefaultCellStyle = { BackColor = Color.FromArgb(248, 248, 248) }
        };
        tabMenu.Controls.Add(dataGridViewMenu);

        // Вкладка Бронирования
        tabReservations = new TabPage("Мои бронирования");
        dataGridViewReservations = new DataGridView
        {
            Dock = DockStyle.Fill,
            AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill,
            ReadOnly = true,
            AllowUserToAddRows = false,
            RowHeadersVisible = false,
            BackgroundColor = Color.White,
            Font = new Font("Segoe UI", 10),
            BorderStyle = BorderStyle.None,
            AlternatingRowsDefaultCellStyle = { BackColor = Color.FromArgb(248, 248, 248) }
        };

        var panelResButtons = new Panel
        {
            Dock = DockStyle.Bottom,
            Height = 50,
            BackColor = Color.FromArgb(245, 245, 245)
        };

        var btnNewReservation = new Button
        {
            Text = "Новое бронирование",
            Location = new Point(10, 10),
            Size = new Size(180, 30),
            Font = new Font("Segoe UI", 10, FontStyle.Bold),
            ForeColor = Color.White,
            BackColor = Color.FromArgb(255, 69, 0),
            FlatStyle = FlatStyle.Flat,
            FlatAppearance = { BorderSize = 0 },
            Cursor = Cursors.Hand
        };
        btnNewReservation.Click += BtnNewReservation_Click;

        var btnDeleteReservation = new Button
        {
            Text = "Удалить",
            Location = new Point(200, 10),
            Size = new Size(100, 30),
            Font = new Font("Segoe UI", 10),
            ForeColor = Color.White,
            BackColor = Color.FromArgb(200, 50, 50),
            FlatStyle = FlatStyle.Flat,
            FlatAppearance = { BorderSize = 0 },
            Cursor = Cursors.Hand
        };
        btnDeleteReservation.Click += BtnDeleteReservation_Click;

        panelResButtons.Controls.Add(btnNewReservation);
        panelResButtons.Controls.Add(btnDeleteReservation);
        tabReservations.Controls.Add(dataGridViewReservations);
        tabReservations.Controls.Add(panelResButtons);

        // Вкладка Профиль
        tabProfile = new TabPage("Профиль");
        var lblInfo = new Label
        {
            Text = $"Имя: {_user["firstName"]}\nФамилия: {_user["lastName"]}\nЛогин: {_user["username"]}",
            Font = new Font("Segoe UI", 14),
            ForeColor = Color.FromArgb(74, 44, 42),
            Location = new Point(30, 30),
            Size = new Size(400, 100),
            TextAlign = ContentAlignment.TopLeft
        };
        tabProfile.Controls.Add(lblInfo);

        tabControl.Controls.Add(tabMenu);
        tabControl.Controls.Add(tabReservations);
        tabControl.Controls.Add(tabProfile);

        this.Controls.Add(tabControl);
        this.Controls.Add(panelHeader);
    }

    private void LoadMenu()
    {
        var items = DatabaseHelper.GetMenuItems();
        if (items.Count == 0) return;

        var dt = new System.Data.DataTable();
        dt.Columns.Add("ID", typeof(int));
        dt.Columns.Add("Название", typeof(string));
        dt.Columns.Add("Категория", typeof(string));
        dt.Columns.Add("Цена", typeof(string));
        dt.Columns.Add("Описание", typeof(string));

        foreach (var item in items)
        {
            dt.Rows.Add(
                item["id"],
                item["name"],
                item["category"],
                $"{item["price"]:F2} ₽",
                item["description"]
            );
        }
        dataGridViewMenu.DataSource = dt;
        dataGridViewMenu.Columns["ID"].Visible = false;
    }

    private void LoadReservations()
    {
        int userId = int.Parse(_user["id"]);
        var list = DatabaseHelper.GetReservations(userId);

        var dt = new System.Data.DataTable();
        dt.Columns.Add("ID", typeof(int));
        dt.Columns.Add("Гость", typeof(string));
        dt.Columns.Add("Телефон", typeof(string));
        dt.Columns.Add("Гостей", typeof(int));
        dt.Columns.Add("Дата", typeof(string));
        dt.Columns.Add("Время", typeof(string));
        dt.Columns.Add("Статус", typeof(string));

        foreach (var item in list)
        {
            dt.Rows.Add(
                item["id"],
                item["guest_name"],
                item["phone"],
                item["guests_count"],
                item["reservation_date"],
                item["reservation_time"],
                item["status"] switch
                {
                    "pending" => "Ожидает",
                    "confirmed" => "Подтверждено",
                    "cancelled" => "Отменено",
                    _ => item["status"]
                }
            );
        }
        dataGridViewReservations.DataSource = dt;
        dataGridViewReservations.Columns["ID"].Visible = false;
    }

    private void BtnNewReservation_Click(object? sender, EventArgs e)
    {
        var form = new ReservationForm(int.Parse(_user["id"]));
        if (form.ShowDialog() == DialogResult.OK)
            LoadReservations();
    }

    private void BtnDeleteReservation_Click(object? sender, EventArgs e)
    {
        if (dataGridViewReservations.CurrentRow == null) return;
        int id = (int)dataGridViewReservations.CurrentRow.Cells[0].Value;
        if (MessageBox.Show("Удалить бронирование?", "Подтверждение", MessageBoxButtons.YesNo, MessageBoxIcon.Question) == DialogResult.Yes)
        {
            DatabaseHelper.DeleteReservation(id);
            LoadReservations();
        }
    }
}
