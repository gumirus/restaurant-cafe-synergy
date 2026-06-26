using System.Drawing;
using System.Windows.Forms;

namespace RestaurantApp.Forms;

public class RegisterForm : Form
{
    private Panel panelTop;
    private Panel panelMain;
    private Label labelTitle;
    private Label labelClose;
    private TextBox textBoxFirstName;
    private TextBox textBoxLastName;
    private TextBox textBoxUsername;
    private TextBox textBoxPassword;
    private TextBox textBoxConfirmPassword;
    private Button buttonRegister;
    private Label labelLogin;

    public RegisterForm()
    {
        InitializeComponent();
    }

    private void InitializeComponent()
    {
        this.Size = new Size(400, 620);
        this.StartPosition = FormStartPosition.CenterScreen;
        this.FormBorderStyle = FormBorderStyle.None;
        this.BackColor = Color.FromArgb(245, 245, 245);

        panelTop = new Panel
        {
            Dock = DockStyle.Top,
            Height = 120,
            BackColor = Color.FromArgb(255, 69, 0)
        };

        labelTitle = new Label
        {
            Text = "Регистрация",
            Font = new Font("Segoe UI", 22, FontStyle.Bold),
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
            Padding = new Padding(40, 20, 40, 0)
        };

        int yStart = 20;
        int spacing = 55;

        AddField(panelMain, "Имя", ref textBoxFirstName, yStart);
        AddField(panelMain, "Фамилия", ref textBoxLastName, yStart + spacing);
        AddField(panelMain, "Логин", ref textBoxUsername, yStart + spacing * 2);
        AddPasswordField(panelMain, "Пароль", ref textBoxPassword, yStart + spacing * 3);
        AddPasswordField(panelMain, "Подтвердите пароль", ref textBoxConfirmPassword, yStart + spacing * 4);

        buttonRegister = new Button
        {
            Text = "Зарегистрироваться",
            Location = new Point(40, yStart + spacing * 5 - 10),
            Size = new Size(280, 45),
            Font = new Font("Segoe UI", 13, FontStyle.Bold),
            ForeColor = Color.White,
            BackColor = Color.FromArgb(255, 69, 0),
            FlatStyle = FlatStyle.Flat,
            FlatAppearance = { BorderSize = 0 },
            Cursor = Cursors.Hand
        };
        buttonRegister.Click += ButtonRegister_Click;

        labelLogin = new Label
        {
            Text = "Уже есть аккаунт? Войти",
            Font = new Font("Segoe UI", 10),
            ForeColor = Color.FromArgb(74, 44, 42),
            Location = new Point(85, yStart + spacing * 6 - 20),
            Size = new Size(200, 25),
            TextAlign = ContentAlignment.MiddleCenter,
            Cursor = Cursors.Hand
        };
        labelLogin.Click += (s, e) =>
        {
            var loginForm = new LoginForm();
            loginForm.Show();
            this.Hide();
        };

        panelMain.Controls.Add(buttonRegister);
        panelMain.Controls.Add(labelLogin);

        this.Controls.Add(panelMain);
        this.Controls.Add(panelTop);
    }

    private void AddField(Panel parent, string placeholder, ref TextBox textBox, int y)
    {
        var lbl = new Label
        {
            Text = placeholder,
            Font = new Font("Segoe UI", 9, FontStyle.Regular),
            ForeColor = Color.Gray,
            Location = new Point(15, y),
            Size = new Size(280, 15)
        };

        textBox = new TextBox
        {
            Location = new Point(15, y + 18),
            Size = new Size(280, 35),
            Font = new Font("Segoe UI", 12),
            ForeColor = Color.FromArgb(74, 44, 42),
            BackColor = Color.FromArgb(245, 245, 245),
            BorderStyle = BorderStyle.None
        };

        parent.Controls.Add(lbl);
        parent.Controls.Add(textBox);
    }

    private void AddPasswordField(Panel parent, string placeholder, ref TextBox textBox, int y)
    {
        var lbl = new Label
        {
            Text = placeholder,
            Font = new Font("Segoe UI", 9, FontStyle.Regular),
            ForeColor = Color.Gray,
            Location = new Point(15, y),
            Size = new Size(280, 15)
        };

        textBox = new TextBox
        {
            Location = new Point(15, y + 18),
            Size = new Size(280, 35),
            Font = new Font("Segoe UI", 12),
            ForeColor = Color.FromArgb(74, 44, 42),
            BackColor = Color.FromArgb(245, 245, 245),
            BorderStyle = BorderStyle.None,
            UseSystemPasswordChar = true
        };

        parent.Controls.Add(lbl);
        parent.Controls.Add(textBox);
    }

    private void ButtonRegister_Click(object? sender, EventArgs e)
    {
        string firstName = textBoxFirstName.Text.Trim();
        string lastName = textBoxLastName.Text.Trim();
        string username = textBoxUsername.Text.Trim();
        string password = textBoxPassword.Text;
        string confirm = textBoxConfirmPassword.Text;

        if (string.IsNullOrWhiteSpace(firstName) || string.IsNullOrWhiteSpace(lastName) ||
            string.IsNullOrWhiteSpace(username) || string.IsNullOrWhiteSpace(password))
        {
            MessageBox.Show("Заполните все поля!", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            return;
        }

        if (password != confirm)
        {
            MessageBox.Show("Пароли не совпадают!", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            return;
        }

        if (password.Length < 4)
        {
            MessageBox.Show("Пароль должен быть не менее 4 символов!", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            return;
        }

        string result = DatabaseHelper.RegisterUser(firstName, lastName, username, password);
        if (result == "ok")
        {
            MessageBox.Show("Регистрация успешна! Теперь вы можете войти.", "Успех", MessageBoxButtons.OK, MessageBoxIcon.Information);
            var loginForm = new LoginForm();
            loginForm.Show();
            this.Hide();
        }
        else
        {
            MessageBox.Show(result, "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Error);
        }
    }
}
