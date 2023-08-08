module.exports = async (page, scenario, vp) => {
  console.log('SCENARIO > ' + scenario.label + ' ' + vp.label);

  await require('./clickAndHoverHelper')(page, scenario);
}
