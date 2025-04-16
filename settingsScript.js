document.addEventListener('DOMContentLoaded', function()
{
    const settingsButton = document.getElementById('settingsButton');
    const settingsDropdown = document.getElementById('settingsDropdown');

    settingsButton.addEventListener('click', function()
    {
        if (settingsDropdown.style.display === 'block')
        {
            settingsDropdown.style.display = 'none';
        }
        else
        {
            settingsDropdown.style.display = 'block';
        }
    });

    document.addEventListener('click', function(event)
    {
        if (!event.target.matches('#settingsButton'))
        {
            settingsDropdown.style.display = 'none';
        }
    });
});
