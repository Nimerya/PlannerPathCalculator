import random, time

attributes = ['"name"','"attr1"','"attr2"','"attr3"','"attr4"','"attr5"','"attr6"','"attr7"','"attr8"','"attr9"','"attr10"']
ranges = {'"attr1"': [1.3, 4.5],'"attr2"': [1.3, 4.5],'"attr3"': [1.3, 4.5],'"attr4"': [1.3, 4.5],'"attr5"': [1.3, 4.5],'"attr6"': [1.3, 4.5],'"attr7"': [1.3, 4.5],'"attr8"': [1.3, 4.5],'"attr9"': [1.3, 4.5],'"attr10"': [1.3, 4.5]}
attr = []
file = open('Nodi1.csv', 'w')


for i in range(0,len(attributes)):
    if i < len(attributes) - 1:
        file.write(attributes[i] + ',')
    elif i == len(attributes) - 1:
        file.write(attributes[i])
file.write('\n')
split_size = 2000000
depth = 1
nodes = int((split_size**(depth + 1) - 1)/(split_size - 1))
start = time.clock()
for i in range(1,nodes + 1):
    s = '"vertex_' + str(i) + '",'
    for j in range(1,len(attributes)):
        s = s + '"' + str(round(random.uniform(ranges[attributes[j]][0],ranges[attributes[j]][1]),2)) + '",'
    s = s[:-1]
    file.write(s + '\n')
print(time.clock() - start)