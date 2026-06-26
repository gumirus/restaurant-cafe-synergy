using System.Drawing;
using System.Windows.Forms;

namespace RestaurantApp.Forms;

public class LoginForm : Form
{
    private Panel panelTop;
    private Panel panelMain;
    private Label labelTitle;
    private Label labelClose;
    private PictureBox pictureBoxUser;
    private PictureBox pictureBoxLock;
    private TextBox textBoxUsername;
    private TextBox textBoxPassword;
    private Button buttonLogin;
    private Label labelRegister;

    public LoginForm()
    {
        InitializeComponent();
    }

    private void InitializeComponent()
    {
        this.Size = new Size(400, 550);
        this.StartPosition = FormStartPosition.CenterScreen;
        this.FormBorderStyle = FormBorderStyle.None;
        this.BackColor = Color.FromArgb(245, 245, 245);

        panelTop = new Panel
        {
            Dock = DockStyle.Top,
            Height = 180,
            BackColor = Color.FromArgb(255, 69, 0)
        };

        labelTitle = new Label
        {
            Text = "Ресторан\nFlavorHouse Bistro",
            Font = new Font("Segoe UI", 20, FontStyle.Bold),
            ForeColor = Color.White,
            Dock = DockStyle.Fill,
            TextAlign = ContentAlignment.MiddleCenter
        };

        labelClose = new Label
        {
            Text = "X",
            Font = new Font("Segoe UI", 14, FontStyle.Bold),
            ForeColor = Color.White,
            Location = new Point(365, 10),
            Size = new Size(25, 25),
            TextAlign = ContentAlignment.MiddleCenter,
            Cursor = Cursors.Hand
        };
        labelClose.MouseEnter += (s, e) => labelClose.ForeColor = Color.DarkOrange;
        labelClose.MouseLeave += (s, e) => labelClose.ForeColor = Color.White;
        labelClose.Click += (s, e) => Application.Exit();

        panelTop.Controls.Add(labelTitle);
        panelTop.Controls.Add(labelClose);

        panelMain = new Panel
        {
            Dock = DockStyle.Fill,
            BackColor = Color.White,
            Padding = new Padding(40, 30, 40, 0)
        };

        // Иконка пользователя (рисуем программно)
        pictureBoxUser = new PictureBox
        {
            Location = new Point(40, 40),
            Size = new Size(30, 30),
            SizeMode = PictureBoxSizeMode.Zoom,
            BackColor = Color.White
        };
        var bmp = new Bitmap(30, 30);
        using (var g = Graphics.FromImage(bmp))
        {
            g.Clear(Color.White);
            using (var brush = new SolidBrush(Color.Gray))
            {
                g.FillEllipse(brush, 8, 3, 14, 14);
                g.FillEllipse(brush, 3, 18, 24, 12);
            }
        }
        pictureBoxUser.Image = bmp;

        textBoxUsername = new TextBox
        {
            Location = new Point(80, 40),
            Size = new Size(240, 35),
            Font = new Font("Segoe UI", 12),
            ForeColor = Color.FromArgb(74, 44, 42),
            BackColor = Color.FromArgb(245, 245, 245),
            BorderStyle = BorderStyle.None,
            Text = "Логин"
        };
        textBoxUsername.Enter += (s, e) => { if (textBoxUsername.Text == "Логин") textBoxUsername.Text = ""; };
        textBoxUsername.Leave += (s, e) => { if (string.IsNullOrWhiteSpace(textBoxUsername.Text)) textBoxUsername.Text = "Логин"; };

        pictureBoxLock = new PictureBox
        {
            Location = new Point(40, 100),
            Size = new Size(30, 30),
            SizeMode = PictureBoxSizeMode.Zoom,
            BackColor = Color.White
        };
        var bmp2 = new Bitmap(30, 30);
        using (var g = Graphics.FromImage(bmp2))
        {
            g.Clear(Color.White);
            using (var brush = new SolidBrush(Color.Gray))
            {
                g.FillRectangle(brush, 8, 12, 14, 16);
                g.DrawArc(new Pen(brush, 3), 10, 6, 10, 10, 180, 180);
            }
        }
        pictureBoxLock.Image = bmp2;

        textBoxPassword = new TextBox
        {
            Location = new Point(80, 100),
            Size = new Size(240, 35),
            Font = new Font("Segoe UI", 12),
            ForeColor = Color.FromArgb(74, 44, 42),
            BackColor = Color.FromArgb(245, 245, 245),
            BorderStyle = BorderStyle.None,
            UseSystemPasswordChar = true,
            Text = "Пароль"
        };
        textBoxPassword.GotFocus += (s, e) =>
        {
            if (textBoxPassword.Text == "Пароль") textBoxPassword.Text = "";
            textBoxPassword.UseSystemPasswordChar = true;
        };
        textBoxPassword.LostFocus += (s, e) =>
        {
            if (string.IsNullOrWhiteSpace(textBoxPassword.Text))
            {
                textBoxPassword.UseSystemPasswordChar = false;
                textBoxPassword.Text = "Пароль";
            }
        };

        buttonLogin = new Button
        {
            Text = "Войти",
            Location = new Point(40, 170),
            Size = new Size(280, 45),
            Font = new Font("Segoe UI", 14, FontStyle.Bold),
            ForeColor = Color.White,
            BackColor = Color.FromArgb(255, 69, 0),
            FlatStyle = FlatStyle.Flat,
            FlatAppearance = { BorderSize = 0 },
            Cursor = Cursors.Hand
        };
        buttonLogin.Click += ButtonLogin_Click;

        labelRegister = new Label
        {
            Text = "Нет аккаунта? Зарегистрироваться",
            Font = new Font("Segoe UI", 10),
            ForeColor = Color.FromArgb(74, 44, 42),
            Location = new Point(70, 230),
            Size = new Size(250, 25),
            TextAlign = ContentAlignment.MiddleCenter,
            Cursor = Cursors.Hand
        };
        labelRegister.Click += (s, e) =>
        {
            var regForm = new RegisterForm();
            regForm.Show();
            this.Hide();
        };

        panelMain.Controls.Add(pictureBoxUser);
        panelMain.Controls.Add(textBoxUsername);
        panelMain.Controls.Add(pictureBoxLock);
        panelMain.Controls.Add(textBoxPassword);
        panelMain.Controls.Add(buttonLogin);
        panelMain.Controls.Add(labelRegister);

        this.Controls.Add(panelMain);
        this.Controls.Add(panelTop);
    }

    private void ButtonLogin_Click(object? sender, EventArgs e)
    {
        string username = textBoxUsername.Text;
        string password = textBoxPassword.Text;

        if (username == "Логин" || password == "Пароль" || string.IsNullOrWhiteSpace(username) || string.IsNullOrWhiteSpace(password))
        {
            MessageBox.Show("Введите логин и пароль!", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            return;
        }

        var user = DatabaseHelper.AuthenticateUser(username, password);
        if (user != null)
        {
            MessageBox.Show($"Добро пожаловать, {user["name"]}!", "Успех", MessageBoxButtons.OK, MessageBoxIcon.Information);
            var mainForm = new MainForm(user);
            mainForm.Show();
            this.Hide();
        }
        else
        {
            MessageBox.Show("Неверный логин или пароль!", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Error);
        }
    }
}
