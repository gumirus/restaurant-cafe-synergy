using System.Drawing;
using System.Windows.Forms;

namespace RestaurantApp.Forms;

public class ReservationForm : Form
{
    private int _userId;
    private TextBox textBoxName;
    private TextBox textBoxPhone;
    private NumericUpDown numericUpDownGuests;
    private DateTimePicker dateTimePickerDate;
    private DateTimePicker dateTimePickerTime;
    private Button buttonSave;
    private Button buttonCancel;

    public ReservationForm(int userId)
    {
        _userId = userId;
        InitializeComponent();
    }

    private void InitializeComponent()
    {
        this.Size = new Size(350, 380);
        this.StartPosition = FormStartPosition.CenterParent;
        this.FormBorderStyle = FormBorderStyle.FixedDialog;
        this.MaximizeBox = false;
        this.MinimizeBox = false;
        this.Text = "Новое бронирование";
        this.BackColor = Color.White;

        int y = 20;
        int spacing = 50;

        var lblName = new Label { Text = "Имя гостя", Location = new Point(20, y), Size = new Size(300, 20) };
        textBoxName = new TextBox { Location = new Point(20, y + 18), Size = new Size(290, 25) };

        var lblPhone = new Label { Text = "Телефон", Location = new Point(20, y + spacing), Size = new Size(300, 20) };
        textBoxPhone = new TextBox { Location = new Point(20, y + spacing + 18), Size = new Size(290, 25) };

        var lblGuests = new Label { Text = "Количество гостей", Location = new Point(20, y + spacing * 2), Size = new Size(300, 20) };
        numericUpDownGuests = new NumericUpDown { Location = new Point(20, y + spacing * 2 + 18), Size = new Size(290, 25), Minimum = 1, Maximum = 20, Value = 2 };

        var lblDate = new Label { Text = "Дата", Location = new Point(20, y + spacing * 3), Size = new Size(300, 20) };
        dateTimePickerDate = new DateTimePicker { Location = new Point(20, y + spacing * 3 + 18), Size = new Size(290, 25), MinDate = DateTime.Today, Format = DateTimePickerFormat.Short };

        var lblTime = new Label { Text = "Время", Location = new Point(20, y + spacing * 4), Size = new Size(300, 20) };
        dateTimePickerTime = new DateTimePicker { Location = new Point(20, y + spacing * 4 + 18), Size = new Size(290, 25), Format = DateTimePickerFormat.Time, ShowUpDown = true, Value = DateTime.Today.AddHours(18) };

        buttonSave = new Button
        {
            Text = "Забронировать",
            Location = new Point(20, y + spacing * 5),
            Size = new Size(140, 30),
            BackColor = Color.FromArgb(255, 69, 0),
            ForeColor = Color.White,
            FlatStyle = FlatStyle.Flat,
            FlatAppearance = { BorderSize = 0 },
            Cursor = Cursors.Hand
        };
        buttonSave.Click += ButtonSave_Click;

        buttonCancel = new Button
        {
            Text = "Отмена",
            Location = new Point(170, y + spacing * 5),
            Size = new Size(140, 30),
            BackColor = Color.Gray,
            ForeColor = Color.White,
            FlatStyle = FlatStyle.Flat,
            FlatAppearance = { BorderSize = 0 },
            Cursor = Cursors.Hand,
            DialogResult = DialogResult.Cancel
        };

        this.Controls.Add(lblName);
        this.Controls.Add(textBoxName);
        this.Controls.Add(lblPhone);
        this.Controls.Add(textBoxPhone);
        this.Controls.Add(lblGuests);
        this.Controls.Add(numericUpDownGuests);
        this.Controls.Add(lblDate);
        this.Controls.Add(dateTimePickerDate);
        this.Controls.Add(lblTime);
        this.Controls.Add(dateTimePickerTime);
        this.Controls.Add(buttonSave);
        this.Controls.Add(buttonCancel);
    }

    private void ButtonSave_Click(object? sender, EventArgs e)
    {
        if (string.IsNullOrWhiteSpace(textBoxName.Text) || string.IsNullOrWhiteSpace(textBoxPhone.Text))
        {
            MessageBox.Show("Заполните имя и телефон!", "Ошибка", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            return;
        }

        bool result = DatabaseHelper.AddReservation(
            _userId,
            textBoxName.Text.Trim(),
            textBoxPhone.Text.Trim(),
            (int)numericUpDownGuests.Value,
            dateTimePickerDate.Value.ToString("yyyy-MM-dd"),
            dateTimePickerTime.Value.ToString("HH:mm:ss")
        );

        if (result)
        {
            MessageBox.Show("Бронирование создано!", "Успех", MessageBoxButtons.OK, MessageBoxIcon.Information);
            this.DialogResult = DialogResult.OK;
            this.Close();
        }
    }
}
