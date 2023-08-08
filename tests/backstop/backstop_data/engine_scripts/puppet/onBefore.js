module.exports = async(page, scenario, vp) => {

  await page.authenticate({username: 'klant', password: 'KlantLogin'});
  //console.log('Using basic authorisation for "klant"');

  // await require('./loadCookies')(page, scenario);
  await require('./interceptImages')(page, scenario);

};
