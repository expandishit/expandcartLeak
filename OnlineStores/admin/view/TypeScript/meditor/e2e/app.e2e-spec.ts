import { ExpandcartMeditorPage } from './app.po';

describe('expandcart-meditor App', function() {
  let page: ExpandcartMeditorPage;

  beforeEach(() => {
    page = new ExpandcartMeditorPage();
  });

  it('should display message saying app works', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('app works!');
  });
});
