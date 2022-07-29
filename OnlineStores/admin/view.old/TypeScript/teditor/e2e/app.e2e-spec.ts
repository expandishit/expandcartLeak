import { ExpandcartTeditorPage } from './app.po';

describe('expandcart-teditor App', function() {
  let page: ExpandcartTeditorPage;

  beforeEach(() => {
    page = new ExpandcartTeditorPage();
  });

  it('should display message saying app works', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('app works!');
  });
});
