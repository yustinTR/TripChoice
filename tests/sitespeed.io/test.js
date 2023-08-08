module.exports = async function(context, commands) {
  await commands.measure.start('https://t-drupal-base-develop.finalist.nl/', 'Homepage');
  await commands.measure.start('https://t-drupal-base-develop.finalist.nl/veelgestelde-vragen', 'FAQ');
};
